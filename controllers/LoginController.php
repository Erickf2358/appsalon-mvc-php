<?php
namespace Controller;
use MVC\Router;
use Model\Usuario;
use Classes\Email;


class LoginController{

    public static function login(Router $router){
        $alertas = [];
        $auth = new Usuario();

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $auth = new Usuario($_POST);
            $alertas = $auth->validarLogin();

            if(empty($alertas)) {
                $usuario = Usuario::where('email', $auth->email);

                if($usuario) {
                    /** @var Usuario $usuario */
                    if($usuario->comprobarPasswordAndVerificado($auth->password)) {
                        if (session_status() === PHP_SESSION_NONE) { session_start(); }
                        session_regenerate_id(true);
                        $_SESSION['id'] = $usuario->id;
                        $_SESSION['nombre'] = $usuario->nombre . " " . $usuario->apellido;
                        $_SESSION['email'] = $usuario->email;
                        $_SESSION['login'] = true;

                        if($usuario->admin === "1") {
                            $_SESSION['admin'] = $usuario->admin;
                            header('Location: /admin');
                        } else {
                            header('Location: /cita');
                        }
                    }
                } else {
                    Usuario::setAlerta('error', 'El usuario no existe');
                }
            }
        }

        $alertas = Usuario::getAlertas();
        $router->render('auth/login', [
            'alertas' => $alertas,
            'usuario' => $auth
        ]);
    }
    

    public static function logout(){
        if (session_status() === PHP_SESSION_NONE) { session_start(); }
        $_SESSION =[];
        header('Location: /');
        
    }


    public static function olvide(Router $router) {
    $alertas = [];
    $usuario = new Usuario();

    if($_SERVER['REQUEST_METHOD'] === 'POST') {
        $usuario->sincronizar($_POST);
        $alertas = $usuario->validarEmail();

        if(empty($alertas)) {
            $usuarioDB = Usuario::where('email', $usuario->email);

            if(!$usuarioDB) {
                Usuario::setAlerta('error', 'El usuario no existe');
            } else {
                /** @var Usuario $usuarioDB */
                if(!$usuarioDB->confirmado) {
                    Usuario::setAlerta('error', 'Tu cuenta no ha sido confirmada');
                } else {
                    $usuarioDB->crearToken();
                    $usuarioDB->guardar();

                    $email = new Email($usuarioDB->email, $usuarioDB->nombre, $usuarioDB->token);
                    $email->enviarInstrucciones();

                    Usuario::setAlerta('exito', 'Revisa tu email para las instrucciones');
                }
            }
        }
    }

    $alertas = Usuario::getAlertas();
    $router->render('auth/olvide-password', [
        'alertas' => $alertas,
        'usuario' => $usuario
    ]);
}

    

    public static function crear(Router $router){   
        $usuario = new Usuario();
        //alertas vacias
        $alertas=[];     

        if($_SERVER['REQUEST_METHOD'] === 'POST') {
            $usuario->sincronizar($_POST);
            $alertas = $usuario->validarNuevaCuenta();

            // revisar alertas este vacio
            if(empty($alertas)) {
                $existeUsuario = Usuario::existeUsuario($usuario->email);
                
                if($existeUsuario) {
                    $alertas = Usuario::getAlertas();
                } else {
                    $usuario->hashPassword();
                    $usuario->crearToken();

                    // enviar el email
                    $email = new Email($usuario->email, $usuario->nombre, $usuario->token);
                    $email->enviarConfirmacion();

                    // guardar en BD
                    $resultado = $usuario->guardar();

                    if($resultado) {
                        header('Location: /mensaje');
                    }
                }
            }
        }

        $router->render('auth/crear-cuenta', [
            'usuario' => $usuario,
            'alertas' => $alertas ?? []
        ]);
    }

    public static function recuperar(Router $router) {
        
        
    
        $router->render('auth/recuperar-password', [

        ]);
    }

    public static function mensaje(Router $router) {
        $router->render('auth/mensaje');
    }


    public static function confirmar(Router $router) {
        $alertas =[];
        $token = s($_GET['token']);
        $usuario = Usuario::where('token',$token);

        if(!$usuario) {
            $alertas = Usuario::setAlerta('error', 'Token no válido');
        } else {
            /** @var Usuario $usuario */
            $usuario->confirmado = 1;
            $usuario->token = null;
            $usuario->guardar();
            $alertas = Usuario::setAlerta('exito', 'Cuenta confirmada correctamente');
        }

        $alertas = Usuario::getAlertas();
        $router->render('auth/confirmar-cuenta', [
            'alertas' => $alertas
        ]);
    }    


}