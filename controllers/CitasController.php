<?php
namespace Controller;
use MVC\Router;

class CitasController {
    public static function index(Router $router) {
        // Iniciar sesión solo si no hay ninguna activa
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['id'])) {
            header('Location: /'); // redirige al home/login
            exit;
        }

        $router->render('cita/index', [
            'nombre' => $_SESSION['nombre'],
            'id' => $_SESSION['id']
        ]);
    }
}