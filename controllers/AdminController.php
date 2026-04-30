<?php
namespace Controller;

use Model\AdminCita;
use MVC\Router;

class AdminController {
    public static function index(Router $router) {
        if (session_status() === PHP_SESSION_NONE) { session_start(); }

        if(!isset($_SESSION['login']) || !isset($_SESSION['admin']) || $_SESSION['admin'] !== '1') {
            header('Location: /');
            exit;
        }
        $fecha = $_GET['fecha'] ?? date('Y-m-d');
        // Validate format YYYY-MM-DD to prevent SQL injection
        if(!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            $fecha = date('Y-m-d');
        }

        $consulta = "SELECT citas.id, citas.hora, CONCAT( usuarios.nombre, ' ', usuarios.apellido) as cliente, ";
        $consulta .= " usuarios.email, usuarios.telefono, servicios.nombre as servicio, servicios.precio  ";
        $consulta .= " FROM citas  ";
        $consulta .= " LEFT OUTER JOIN usuarios ";
        $consulta .= " ON citas.usuarioId=usuarios.id  ";
        $consulta .= " LEFT OUTER JOIN citasservicios ";
        $consulta .= " ON citasservicios.citaId=citas.id ";
        $consulta .= " LEFT OUTER JOIN servicios ";
        $consulta .= " ON servicios.id=citasservicios.servicioId ";
        $consulta .= " WHERE citas.fecha = '{$fecha}' ";

        $citas = AdminCita::SQL($consulta);

        $router->render('admin/index',[
            'nombre' => $_SESSION['nombre'] ?? '',
            'fecha'  => $fecha,
            'citas'  => $citas
        ]);
    }
}

