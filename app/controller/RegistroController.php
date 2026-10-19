<?php

namespace app\controller;

class RegistroController
{
    private $render;
    private $usuarioModel;

    public function __construct($render, $usuarioModel){
        $this->render = $render;
        $this->usuarioModel = $usuarioModel;
    }

    public function show(){
        $this->render->renderiza('registro');
    }

    public function registrar()
    {
        $nombre = $_POST['nombre'];
        $usuario = $_POST['usuario'];
        $email = $_POST['email'];
        $fechaNacimiento = $_POST['fecha_nacimiento'];
        $sexo = $_POST['sexo'];
        $pais = $_POST['pais'];
        $ciudad = $_POST['ciudad'];

        $password = $_POST['password'];
        $confirmPassword = $_POST['confirm_password'];

        // Validar contraseñas
        if ($password !== $confirmPassword) {
            $this->render->renderiza("registro", [
                "error" => "Las contraseñas no coinciden."
            ]);
            return;
        }

        // Validar usuario existente
        if ($this->usuarioModel->existeUsuario($usuario)) {
            $this->render->renderiza("registro", [
                "error" => "El nombre de usuario ya está registrado."
            ]);
            return;
        }

        // Validar email existente
        if ($this->usuarioModel->existeEmail($email)) {
            $this->render->renderiza("registro", [
                "error" => "El email ya se encuentra registrado."
            ]);
            return;
        }

        // Hash seguro de contraseña
        $passwordHash = password_hash(
            $password,
            PASSWORD_DEFAULT
        );

        // Foto por defecto
        $foto = "default.png";

        // Crear usuario
        $this->usuarioModel->crearUsuario(
            $nombre,
            $usuario,
            $email,
            $passwordHash,
            $fechaNacimiento,
            $sexo,
            $pais,
            $ciudad,
            $foto
        );

        // Redirige al login
        header("Location: /web2-unlam-tp-final/login/show?registro=ok");
        exit();
    }
}