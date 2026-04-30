<?php
namespace Classes;

use PHPMailer\PHPMailer\PHPMailer;

class Email {

    public $email;
    public $nombre;
    public $token;

    public function __construct($email, $nombre, $token) {
        $this->email = $email;
        $this->nombre = $nombre;
        $this->token = $token;
    }

    public function enviarConfirmacion() {
        $mail = new PHPMailer();
        $mail->isSMTP();
        $mail->Host = $_ENV['EMAIL_HOST'];
        $mail->SMTPAuth = true;
        $mail->Port = $_ENV['EMAIL_PORT'];
        $mail->Username = $_ENV['EMAIL_USER'];
        $mail->Password = $_ENV['EMAIL_PASS'];

        $mail->setFrom('cuentas@appsalon.com', 'AppSalon');
        $mail->addAddress($this->email, $this->nombre);
        $mail->Subject = 'Confirma tu cuenta';
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';

        $mail->Body = "
            <h1>Hola {$this->nombre}</h1>
            <p>Tu cuenta ya está lista, solo debes confirmarla haciendo click en el siguiente enlace:</p>
            <a href='{$_ENV['APP_URL']}/confirmar-cuenta?token={$this->token}'>Confirmar cuenta</a>
            <p>Si no creaste esta cuenta, ignora este mensaje</p>
        ";

        $mail->send();
    }

    public function enviarInstrucciones() {
        $mail = new PHPMailer();
        $mail->isSMTP();
        $mail->Host = $_ENV['EMAIL_HOST'];
        $mail->SMTPAuth = true;
        $mail->Port = $_ENV['EMAIL_PORT'];
        $mail->Username = $_ENV['EMAIL_USER'];
        $mail->Password = $_ENV['EMAIL_PASS'];
        $mail->CharSet = 'UTF-8';

        $mail->setFrom('cuentas@appsalon.com', 'AppSalon');
        $mail->addAddress($this->email, $this->nombre);
        $mail->Subject = 'Restablecer tu password';
        $mail->isHTML(true);
        $mail->Body = "
            <h1>Hola {$this->nombre}</h1>
            <p>Haz click en el siguiente enlace para restablecer tu password:</p>
            <a href='{$_ENV['APP_URL']}/recuperar?token={$this->token}'>Recuperar Password</a>
            <p>Si no solicitaste esto, ignora este mensaje</p>
        ";

        $mail->send();
    }
}

