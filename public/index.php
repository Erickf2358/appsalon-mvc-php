<?php  

require_once __DIR__ . '/../includes/app.php';

use Controller\AdminController;
use Controller\LoginController;
use Controller\CitasController;
use Controller\APIController;

use MVC\Router;

$router = new Router();

// iniciar session
$router->get('/',[LoginController::class, 'login']);
$router->post('/',[LoginController::class, 'login']);
$router->get('/logout',[LoginController::class, 'logout']);


// Recuperar password
$router->get('/olvide-password',[LoginController::class, 'olvide']);
$router->post('/olvide-password',[LoginController::class, 'olvide']);
$router->get('/recuperar',[LoginController::class, 'recuperar']);
$router->post('/recuperar',[LoginController::class, 'recuperar']);

$router->get('/crear-cuenta', [LoginController::class, 'crear']);
$router->post('/crear-cuenta', [LoginController::class, 'crear']);

// confirmar cuenta
$router->get('/confirmar-cuenta', [LoginController::class, 'confirmar']);
$router->get('/mensaje', [LoginController::class, 'mensaje']);

//area privada
$router->get('/cita', [CitasController::class, 'index']);
$router->get('/admin', [AdminController::class, 'index']);


// API
$router->get('/api/servicios', [APIController::class, 'index']);
$router->post('/api/citas', [APIController::class, 'guardar']);
$router->get('/api/citas', [APIController::class, 'obtenerCitas']);
$router->get('/api/cita-detalle', [APIController::class, 'obtenerDetalleCita']);
$router->post('/api/citas/eliminar', [APIController::class, 'eliminarCita']);

// Admin API
$router->get('/api/admin/cita-detalle', [APIController::class, 'obtenerDetalleCitaAdmin']);
$router->post('/api/admin/citas/eliminar', [APIController::class, 'eliminarCitaAdmin']);
$router->post('/api/admin/citas/actualizar', [APIController::class, 'actualizarCitaAdmin']);

// Comprueba y valida las rutas, que existan y les asigna las funciones del Controlador
$router->comprobarRutas();