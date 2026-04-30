<?php
namespace Controller;
use MVC\Router;
use Model\Servicio;
use Model\Cita;
use Model\CitaServicio;
use Model\ActiveRecord;

class APIController {
    public static function index(Router $router) {
        $servicios = Servicio::all();
        echo json_encode($servicios);
    }

    public static function guardar(Router $router) {

        header('Content-Type: application/json');

        if (session_status() === PHP_SESSION_NONE) { session_start(); }

        if(!isset($_SESSION['id'])) {
            echo json_encode(['error' => 'Usuario no autenticado']);
            return;
        }

        $hora    = $_POST['hora'] ?? '';
        $partes  = explode(':', $hora);
        $minutos = isset($partes[1]) ? (int) $partes[1] : -1;

        if ($minutos < 0 || $minutos % 15 !== 0) {
            echo json_encode(['error' => 'La hora debe ser en bloques de 15 minutos (ej: 10:00, 10:15, 10:30)']);
            return;
        }
        if ($hora < '10:00' || $hora >= '22:00') {
            echo json_encode(['error' => 'El horario de atención es de 10:00 a 22:00']);
            return;
        }

        $cita = new Cita($_POST);

        // Asignar usuario desde sesión y resetear id para INSERT
        $cita->id = null;
        $cita->usuarioId = $_SESSION['id'];

        $resultado = $cita->guardar();

        if($resultado['resultado']) {
            $citaId = $resultado['id'];

            // Insertar servicios en citasservicios
            $serviciosIds = explode(',', $_POST['servicios'] ?? '');
            foreach($serviciosIds as $servicioId) {
                $servicioId = intval(trim($servicioId));
                if($servicioId <= 0) continue;

                $citaServicio = new CitaServicio([
                    'citaId'     => $citaId,
                    'servicioId' => $servicioId
                ]);
                $citaServicio->guardar();
            }
        }

        echo json_encode($resultado);
    }

    public static function obtenerCitas(Router $router) {
        header('Content-Type: application/json');

        if (session_status() === PHP_SESSION_NONE) { session_start(); }

        if(!isset($_SESSION['id'])) {
            echo json_encode(['error' => 'Usuario no autenticado']);
            return;
        }

        $usuarioId = intval($_SESSION['id']);

        $query = "SELECT c.id, c.fecha, c.hora,
                         GROUP_CONCAT(s.nombre ORDER BY s.nombre SEPARATOR ', ') AS servicios,
                         SUM(s.precio) AS total
                  FROM citas c
                  INNER JOIN citasservicios cs ON cs.citaId = c.id
                  INNER JOIN servicios s ON s.id = cs.servicioId
                  WHERE c.usuarioId = {$usuarioId}
                    AND c.fecha >= CURDATE()
                  GROUP BY c.id
                  ORDER BY c.fecha ASC, c.hora ASC";

        $citas = ActiveRecord::fetchAll($query);
        echo json_encode($citas);
    }

    public static function obtenerDetalleCita(Router $router) {
        header('Content-Type: application/json');

        if (session_status() === PHP_SESSION_NONE) { session_start(); }

        if(!isset($_SESSION['id'])) {
            echo json_encode(['error' => 'Usuario no autenticado']);
            return;
        }

        $usuarioId = intval($_SESSION['id']);
        $citaId    = intval($_GET['id'] ?? 0);

        if(!$citaId) {
            echo json_encode(['error' => 'ID de cita no válido']);
            return;
        }

        // Verify ownership
        $cita = Cita::find($citaId);
        if(!$cita || intval($cita->usuarioId) !== $usuarioId) {
            echo json_encode(['error' => 'Cita no encontrada']);
            return;
        }

        // Fetch services with full details
        $query = "SELECT s.id, s.nombre, s.precio
                  FROM citasservicios cs
                  INNER JOIN servicios s ON s.id = cs.servicioId
                  WHERE cs.citaId = {$citaId}";

        $servicios = ActiveRecord::fetchAll($query);

        echo json_encode([
            'id'        => $cita->id,
            'fecha'     => $cita->fecha,
            'hora'      => $cita->hora,
            'servicios' => $servicios
        ]);
    }

    public static function eliminarCita(Router $router) {
        header('Content-Type: application/json');

        if (session_status() === PHP_SESSION_NONE) { session_start(); }

        if(!isset($_SESSION['id'])) {
            echo json_encode(['error' => 'Usuario no autenticado']);
            return;
        }

        $usuarioId = intval($_SESSION['id']);
        $citaId    = intval($_POST['id'] ?? 0);

        if(!$citaId) {
            echo json_encode(['error' => 'ID de cita no válido']);
            return;
        }

        // Verify ownership before deleting
        $cita = Cita::find($citaId);
        if(!$cita || intval($cita->usuarioId) !== $usuarioId) {
            echo json_encode(['error' => 'Cita no encontrada o no autorizada']);
            return;
        }

        // Delete related services first (FK integrity)
        $idEscapado = ActiveRecord::escapar($citaId);
        ActiveRecord::ejecutar("DELETE FROM citasservicios WHERE citaId = {$idEscapado}");
        $cita->eliminar();

        echo json_encode(['resultado' => true]);
    }

    // ── Admin-only endpoints ────────────────────────────────────────────────

    private static function verificarAdmin(): bool {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        if(!isset($_SESSION['login']) || !isset($_SESSION['admin']) || $_SESSION['admin'] !== '1') {
            http_response_code(401);
            return false;
        }
        return true;
    }

    public static function obtenerDetalleCitaAdmin(Router $router) {
        header('Content-Type: application/json');

        if(!self::verificarAdmin()) {
            echo json_encode(['error' => 'No autorizado']);
            return;
        }

        $citaId = intval($_GET['id'] ?? 0);
        if(!$citaId) { echo json_encode(['error' => 'ID inválido']); return; }

        $cita = Cita::find($citaId);
        if(!$cita) { echo json_encode(['error' => 'Cita no encontrada']); return; }

        $query = "SELECT s.id, s.nombre, s.precio
                  FROM citasservicios cs
                  INNER JOIN servicios s ON s.id = cs.servicioId
                  WHERE cs.citaId = {$citaId}";

        $servicios = ActiveRecord::fetchAll($query);

        echo json_encode([
            'id'        => $cita->id,
            'fecha'     => $cita->fecha,
            'hora'      => $cita->hora,
            'servicios' => $servicios
        ]);
    }

    public static function eliminarCitaAdmin(Router $router) {
        header('Content-Type: application/json');

        if(!self::verificarAdmin()) {
            echo json_encode(['error' => 'No autorizado']);
            return;
        }

        $citaId = intval($_POST['id'] ?? 0);
        if(!$citaId) { echo json_encode(['error' => 'ID inválido']); return; }

        $cita = Cita::find($citaId);
        if(!$cita) { echo json_encode(['error' => 'Cita no encontrada']); return; }

        $idEsc = ActiveRecord::escapar($citaId);
        ActiveRecord::ejecutar("DELETE FROM citasservicios WHERE citaId = {$idEsc}");
        $cita->eliminar();

        echo json_encode(['resultado' => true]);
    }

    public static function actualizarCitaAdmin(Router $router) {
        header('Content-Type: application/json');

        if(!self::verificarAdmin()) {
            echo json_encode(['error' => 'No autorizado']);
            return;
        }

        $citaId = intval($_POST['id'] ?? 0);
        if(!$citaId) { echo json_encode(['error' => 'ID inválido']); return; }

        $cita = Cita::find($citaId);
        if(!$cita) { echo json_encode(['error' => 'Cita no encontrada']); return; }

        $fecha = $_POST['fecha'] ?? '';
        $hora  = $_POST['hora']  ?? '';

        if(!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha) || !preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $hora)) {
            echo json_encode(['error' => 'Datos de fecha/hora inválidos']);
            return;
        }

        $cita->fecha = ActiveRecord::escapar($fecha);
        $cita->hora  = ActiveRecord::escapar($hora);
        $cita->actualizar();

        // Replace services
        $serviciosIds = array_filter(array_map('intval', explode(',', $_POST['servicios'] ?? '')));
        if(!empty($serviciosIds)) {
            $idEsc = ActiveRecord::escapar($citaId);
            ActiveRecord::ejecutar("DELETE FROM citasservicios WHERE citaId = {$idEsc}");
            foreach($serviciosIds as $servicioId) {
                $cs = new CitaServicio(['citaId' => $citaId, 'servicioId' => $servicioId]);
                $cs->guardar();
            }
        }

        echo json_encode(['resultado' => true]);
    }
}
