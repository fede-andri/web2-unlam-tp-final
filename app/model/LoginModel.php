<?php

namespace app\model;
class LoginModel
{
    private $database;

    public function __construct($database)
    {
        $this->database = $database;
    }

    public function autenticar($usuario, $password)
    {
//        $passwordHash = md5($password);

        // A propósito sin prepared statements: lo arreglamos en la clase de refactor
        $sql = "SELECT * FROM usuarios WHERE usuario = ?";
        $fila = $this->database->queryOne($sql, [$usuario]);

        if ($fila && password_verify($password, $fila['password'])) {
            unset($fila['password']);
            return $fila;
        }

        return null;
    }
}
