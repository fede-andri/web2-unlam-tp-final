<?php

namespace app\controller;

class RegistroController
{
    private $render;
    private $usuarioModel;

    private $mailService;
    private $mapConfig;
    private $appConfig;
    private $baseUrl;
    private $basePathUrl;
    public function __construct($render, $usuarioModel, $mapConfig, $appConfig, $mailService){
        $this->render = $render;
        $this->usuarioModel = $usuarioModel;
        $this->mapConfig = $mapConfig;
        $this->appConfig = $appConfig;
        $this->mailService = $mailService;
        $this->baseUrl = $this->appConfig["base_url"];
        $this->basePathUrl = $this->appConfig["base_path_url"];

    }

    public function show(){
        $data = [

            "tile_layer" => $this->mapConfig["tile_layer"],
            "reverse_geocode" => $this->mapConfig["reverse_geocode"],
            "default_lat" => $this->mapConfig["default_lat"],
            "default_lng" => $this->mapConfig["default_lng"],
            "default_zoom" => $this->mapConfig["default_zoom"],

            "modo_edicion" => true,
            "mensaje_mapa" => "Seleccioná tu ubicación en el mapa.",
            "registrar_url" => $this->basePathUrl . $this->appConfig["registrar_url"],
            "login_show_url" => $this->basePathUrl . $this->appConfig["login_show_url"],
            "base_path_url" => $this->basePathUrl
        ];
        $this->render->renderiza('registro', $data);
    }

    public function registrar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location:' . $this->appConfig['base_path_url'] .$this->appConfig['registro_url']);
            exit();
        }
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

        $tokenValidacion = bin2hex(random_bytes(32));

        $urlValidacion =
            $this->baseUrl . $this->basePathUrl . $this->appConfig["validacion_url"] .
            "?token=" .
            $tokenValidacion;
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
            $foto,
            $tokenValidacion
        );

        $mailEnviado = $this->mailService->enviarValidacionMail($email,$nombre,$urlValidacion);
        // Redirige al login
        header("Location: ". $this->basePathUrl . $this->appConfig['login_show_url'] ."?registro=ok");
        exit();
    }

    public function validar(){
        $token = $_GET['token'];
        if (empty($token)) {
            header("Location: ". $this->basePathUrl . $this->appConfig['login_show_url']);
            exit();
        }

        $usuario = $this->usuarioModel->buscarPorToken($token);

        if (!$usuario) {
            header("Location: ". $this->basePathUrl . $this->appConfig['login_show_url'] ."validacion=error");
            exit();
        }
        $this->usuarioModel->validarUsuario($token);
        header("Location: ". $this->basePathUrl . $this->appConfig['login_show_url'] . "?validacion=ok");
        exit();
    }
}