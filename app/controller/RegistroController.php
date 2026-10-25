<?php

namespace app\controller;

class RegistroController
{
    private $render;
    private $usuarioModel;

    private $mapConfig;

    public function __construct($render, $usuarioModel, $mapConfig){
        $this->render = $render;
        $this->usuarioModel = $usuarioModel;
        $this->mapConfig = $mapConfig;
    }

    public function show(){
        $data = [

            "tile_layer" => $this->mapConfig["tile_layer"],
            "reverse_geocode" => $this->mapConfig["reverse_geocode"],
            "default_lat" => $this->mapConfig["default_lat"],
            "default_lng" => $this->mapConfig["default_lng"],
            "default_zoom" => $this->mapConfig["default_zoom"],

            "modo_edicion" => true,
            "mensaje_mapa" => "Seleccioná tu ubicación en el mapa."
        ];
        $this->render->renderiza('registro', $data);
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
        $latitud = $_POST['latitud'];
        $longitud = $_POST['longitud'];
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
            $latitud,
            $longitud,
            $foto
        );

        // Redirige al login
        header("Location: /web2-unlam-tp-final/login/show?registro=ok");
        exit();
    }
}