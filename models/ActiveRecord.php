<?php
namespace Model;

use PDO;

class ActiveRecord {

    protected static $db;
    protected static $tabla = '';
    protected static $columnasDB = [];
    protected static $alertas = [];

    public static function setDB(PDO $database) {
        self::$db = $database;
    }

    public static function setAlerta($tipo, $mensaje) {
        static::$alertas[$tipo][] = $mensaje;
        return static::$alertas;
    }

    public static function getAlertas() {
        return static::$alertas;
    }

    public function validar() {
        static::$alertas = [];
        return static::$alertas;
    }

    protected static function consultarSQL($query, $params = []) {
        $stmt = self::$db->prepare($query);
        $stmt->execute($params);
        $registros = $stmt->fetchAll();

        $array = [];
        foreach ($registros as $registro) {
            $array[] = static::crearObjeto($registro);
        }
        return $array;
    }

    public static function SQL($query, $params = []) {
        return self::consultarSQL($query, $params);
    }

    public static function fetchAll($query, $params = []) {
        $stmt = self::$db->prepare($query);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function ejecutar($query, $params = []) {
        $stmt = self::$db->prepare($query);
        return $stmt->execute($params);
    }

    protected static function crearObjeto($registro) {
        $objeto = new static;
        foreach ($registro as $key => $value) {
            if (property_exists($objeto, $key)) {
                $objeto->$key = $value;
            }
        }
        return $objeto;
    }

    public function atributos() {
        $atributos = [];
        foreach (static::$columnasDB as $columna) {
            if ($columna === 'id') continue;
            $atributos[$columna] = $this->$columna;
        }
        return $atributos;
    }

    public function sincronizar($args = []) {
        foreach ($args as $key => $value) {
            if (property_exists($this, $key) && $value !== null) {
                $this->$key = $value;
            }
        }
    }

    /* ---------- CRUD ---------- */

    public function guardar() {
        return $this->id ? $this->actualizar() : $this->crear();
    }

    public static function all() {
        $query = "SELECT * FROM " . static::$tabla;
        return self::consultarSQL($query);
    }

    public static function find($id) {
        $query = "SELECT * FROM " . static::$tabla . " WHERE id = :id";
        $resultado = self::consultarSQL($query, ['id' => $id]);
        return array_shift($resultado);
    }

    public static function where($columna, $valor) {
        $query = "SELECT * FROM " . static::$tabla . " WHERE {$columna} = :valor LIMIT 1";
        $resultado = self::consultarSQL($query, ['valor' => $valor]);
        return array_shift($resultado);
    }

    public function crear() {
        $atributos = $this->atributos();

        $columnas = implode(', ', array_keys($atributos));
        $placeholders = ':' . implode(', :', array_keys($atributos));

        $query = "INSERT INTO " . static::$tabla .
                 " ({$columnas}) VALUES ({$placeholders}) RETURNING id";

        $stmt = self::$db->prepare($query);
        $stmt->execute($atributos);

        $this->id = $stmt->fetchColumn();
        return [
            'resultado' => true,
            'id' => $this->id
        ];
    }

    public function actualizar() {
        $atributos = $this->atributos();
        $campos = [];

        foreach ($atributos as $key => $value) {
            $campos[] = "{$key} = :{$key}";
        }

        $atributos['id'] = $this->id;

        $query = "UPDATE " . static::$tabla . " SET " .
                 implode(', ', $campos) .
                 " WHERE id = :id";

        $stmt = self::$db->prepare($query);
        return $stmt->execute($atributos);
    }

    public function eliminar() {
        $query = "DELETE FROM " . static::$tabla . " WHERE id = :id";
        $stmt = self::$db->prepare($query);
        return $stmt->execute(['id' => $this->id]);
    }
}
