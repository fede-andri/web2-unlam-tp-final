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

    public function buscarPorId($id)
    {
        $sql = "SELECT * FROM usuarios WHERE id = ?";
        return $this->database->queryOne($sql, [$id]);
    }

    // Solo datos aptos para mostrar a otros jugadores (sin mail ni fecha de nacimiento).
    public function buscarPerfilPublico($usuario)
    {
        $sql = "SELECT u.id, u.nombre, u.usuario, u.pais, u.ciudad, u.foto,
                       u.latitud, u.longitud,
                       COALESCE(SUM(p.puntaje), 0) AS puntaje_total,
                       COUNT(p.id) AS partidas_jugadas
                FROM usuarios u
                LEFT JOIN partidas p ON p.usuario_id = u.id
                WHERE u.usuario = ? AND u.validado = 1
                GROUP BY u.id, u.nombre, u.usuario, u.pais, u.ciudad, u.foto, u.latitud, u.longitud";
        return $this->database->queryOne($sql, [$usuario]);
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

    public function actualizarFoto($id, $foto)
    {
        $sql = "UPDATE usuarios SET foto = ? WHERE id = ?";
        return $this->database->execute($sql, [$foto, $id]);
    }

    // Para validar que el nuevo nombre de usuario no lo tenga OTRA persona
    public function existeUsuarioDeOtro($usuario, $id)
    {
        $sql = "SELECT id FROM usuarios WHERE usuario = ? AND id <> ?";
        return $this->database->queryOne($sql, [$usuario, $id]);
    }

    public function actualizarPerfil($id, $nombre, $usuario, $fechaNacimiento, $sexo, $pais, $ciudad, $latitud, $longitud)
    {
        $sql = "
        UPDATE usuarios
        SET nombre = ?,
            usuario = ?,
            fecha_nacimiento = ?,
            sexo = ?,
            pais = ?,
            ciudad = ?,
            latitud = ?,
            longitud = ?
        WHERE id = ?
    ";

        return $this->database->execute(
            $sql,
            [$nombre, $usuario, $fechaNacimiento, $sexo, $pais, $ciudad, $latitud, $longitud, $id]
        );
    }

    // Borra el usuario y todo lo que depende de él
    public function eliminarCuenta($id)
    {
        $this->database->execute(
            "DELETE pp FROM partida_pregunta pp
         JOIN partidas p ON p.id = pp.partida_id
         WHERE p.usuario_id = ?",
            [$id]
        );

        $this->database->execute("DELETE FROM partidas WHERE usuario_id = ?", [$id]);

        return $this->database->execute("DELETE FROM usuarios WHERE id = ?", [$id]);
    }
}


