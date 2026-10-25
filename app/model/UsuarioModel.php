<?php

namespace app\model;

class UsuarioModel
{

    private $database;

    public function __construct($database){
        $this->database = $database;
    }

    public function existeUsuario($usuario)
    {
        $sql = " SELECT id FROM usuarios WHERE usuario = ?";
        return $this->database->queryOne($sql, [$usuario]);
    }

    public function existeEmail($email)
    {
        $sql = "SELECT id FROM usuarios WHERE email = ?";
        return $this->database->queryOne($sql, [$email]);
    }

    public function crearUsuario(
        $nombre,
        $usuario,
        $email,
        $password,
        $fechaNacimiento,
        $sexo,
        $pais,
        $ciudad,
        $latitud,
        $longitud,
        $foto = "default.png",
        $tokenValidacion
    ) {

        $sql = "
            INSERT INTO usuarios
            (
                nombre,
                usuario,
                email,
                password,
                fecha_nacimiento,
                sexo,
                pais,
                ciudad,
                latitud,
                longitud,
                foto,
                token_validacion
            )
            VALUES
            (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
            )
       ";

        return $this->database->execute(
            $sql,
            [
                $nombre,
                $usuario,
                $email,
                $password,
                $fechaNacimiento,
                $sexo,
                $pais,
                $ciudad,
                $latitud,
                $longitud,
                $foto,
                $tokenValidacion
            ]
        );
    }

    public function buscarPorToken($token)
    {
        $sql = "
        SELECT *
        FROM usuarios
        WHERE token_validacion = ?
    ";

        return $this->database->queryOne(
            $sql,
            [$token]
        );
    }

    public function validarUsuario($token)
    {
        $sql = "
        UPDATE usuarios
        SET
            validado = 1,
            token_validacion = NULL
        WHERE token_validacion = ?
    ";

        return $this->database->execute(
            $sql,
            [$token]
        );
    }
}