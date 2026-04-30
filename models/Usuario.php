<?php
namespace Model;

class Usuario extends ActiveRecord {
    
    protected static $tabla = 'usuarios';
    protected static $columnasDB = ['id', 'nombre', 'apellido', 'email', 'telefono', 'admin', 'confirmado', 'token', 'password'];

    public $id;
    public $nombre;
    public $apellido;
    public $email;
    public $telefono;
    public $admin;
    public $confirmado;
    public $token;
    public $password;

    public function __construct($args = []) {
        $this->id = $args['id'] ?? null;
        $this->nombre = $args['nombre'] ?? '';
        $this->apellido = $args['apellido'] ?? '';
        $this->email = $args['email'] ?? '';
        $this->telefono = $args['telefono'] ?? '';
        $this->admin = $args['admin'] ?? 0;
        $this->confirmado = $args['confirmado'] ?? 0;
        $this->token = $args['token'] ?? '';
        $this->password = $args['password'] ?? '';
        
    }

    // Validación
    public function validarNuevaCuenta() {
        static::$alertas = [];

        if(!$this->nombre) {
            static::$alertas['error'][] = 'El nombre es obligatorio';
        }
        if(!$this->apellido) {
            static::$alertas['error'][] = 'El apellido es obligatorio';
        }
        if(!$this->telefono) {
            static::$alertas['error'][] = 'El teléfono es obligatorio';
        }
        if(!$this->email) {
            static::$alertas['error'][] = 'El email es obligatorio';
        }
        if(!$this->password) {
            static::$alertas['error'][] = 'El password es obligatorio';
        } elseif(strlen($this->password) < 6) {
            static::$alertas['error'][] = 'El password debe tener al menos 6 caracteres';
        }

        return static::$alertas;
    }

    public function validarLogin() {
        if(!$this->email) {
            self::$alertas['error'][] = 'El email es obligatorio';
        }
        if(!$this->password) {
            self::$alertas['error'][] = 'El password es obligatorio';
        }

        return self::$alertas;
    }

    public function validarEmail() {
        if(!$this->email) {
            self::$alertas['error'][] = 'El email es obligatorio';
        }
        return self::$alertas;
    }
    // revisa si el usuario ya existe
    public static function existeUsuario($email) {
        $emailEscapado = self::$db->escape_string($email);
        $query = "SELECT * FROM " . static::$tabla . " WHERE email = '$emailEscapado' LIMIT 1";
        $resultado = self::$db->query($query);
        if($resultado->num_rows) {
            self::$alertas['error'][] = 'El usuario ya existe';
        }
        return $resultado->num_rows;
    }

    public function hashPassword() {
        $this->password = password_hash($this->password, PASSWORD_BCRYPT);
    }

    public function crearToken() {
        $this->token = bin2hex(random_bytes(32));
    }

    public static function whereToken($token) {
        $tokenEscapado = self::$db->escape_string($token);
        $query = "SELECT * FROM " . static::$tabla . " WHERE token = '$tokenEscapado' LIMIT 1";
        $resultado = self::consultarSQL($query);
        return array_shift($resultado);
    }

    

    public function comprobarPasswordAndVerificado($password) {
        $resultado = password_verify($password, $this->password);
        if(!$resultado || !$this->confirmado) {
            self::$alertas['error'][] = 'Password incorrecto o cuenta no confirmada';
        } else return true;
    }

    

    public function validarPassword() {
        static::$alertas = [];
        if(!$this->password) {
            static::$alertas['error'][] = 'El password es obligatorio';
        }
        if(strlen($this->password) < 6) {
            static::$alertas['error'][] = 'El password debe tener al menos 6 caracteres';
        }
        return static::$alertas;
    }
}