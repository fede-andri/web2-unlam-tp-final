<?php

namespace app\controller;

use Redirect;
use Request;

class LoginController
{
    private $model;
    private $render;
    private $appConfig;
    private $usuarioModel;
    private $mailService;
    public function __construct($model, $render, $appConfig, $mailService, $usuarioModel)
    {
        $this->model = $model;
        $this->render = $render;
        $this->appConfig = $appConfig;
        $this->mailService = $mailService;
        $this->usuarioModel = $usuarioModel;
    }

    public function show()
    {
        if (isset($_SESSION['usuario_id'])) {
            header('Location:' . $this->appConfig['base_path_url'] .$this->appConfig['lobby_url']);
            exit();
        }

        $registroExitoso = false;
        if (isset($_GET['registro']) && $_GET['registro'] === 'ok') {
            $registroExitoso = true;
        }

        $this->render->renderiza('login', [
            'login_error' => isset($_GET['login_error']),
            'registro_exitoso' => $registroExitoso,
            'registro_url' => $this->appConfig["base_path_url"] .$this->appConfig["registro_url"],
            'login_url' => $this->appConfig["base_path_url"] . $this->appConfig["login_url"],
            'usuario_no_validado'=>isset($_GET['usuario_no_validado']),
            'olvide_password_url'=>$this->appConfig["base_path_url"] . $this->appConfig["olvide_password_url"],
            'password_reset'=>isset($_GET['password_reset']),
        ]);
    }
    public function login()
    {
        $usuario  = trim(Request::post('usuario') ?? '');
        $password = Request::post('password') ?? '';

        $datosUsuario = $this->model->autenticar($usuario, $password);
        if ($datosUsuario === null) {
            Redirect::to('/show?login_error=1');
        }

        if (!$datosUsuario['validado']) {
            Redirect::to('/show?usuario_no_validado=1');
        }
//        session_regenerate_id(true);
        $_SESSION['usuario_id'] = $datosUsuario['id'];
        $_SESSION['usuario'] = $datosUsuario['usuario'];
        $_SESSION['rol'] = $datosUsuario['rol'];

        if ($datosUsuario['rol'] === 'ADMIN') {
            header(
                'Location:' .
                $this->appConfig['base_path_url'] .
                $this->appConfig["admin_show_url"]
            );
            exit();
        }

        header('Location:' . $this->appConfig['base_path_url'] .$this->appConfig['lobby_url']);
        exit();
    }

    public function logout()
    {
        $_SESSION = [];
        session_destroy();
        header('Location:' . $this->appConfig['base_path_url'] .$this->appConfig['login_show_url']);
        exit();
    }

    public function olvidePassword(){
        $data = [
            "base_url" => $this->appConfig["base_path_url"],
            "enviar_recuperacion_url" =>
                $this->appConfig["base_path_url"]
                . $this->appConfig["enviar_recuperacion_url"],
            "login_show_url"=>$this->appConfig["base_path_url"].$this->appConfig["login_show_url"],
        ];

        $this->render->renderiza(
            'olvidePassword',
            $data
        );
    }

    public function enviarRecuperacion()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {


            header(
                'Location:' .
                $this->appConfig['base_path_url'] .
                $this->appConfig['olvide_password_url']
            );

            exit();
        }

        $email = trim($_POST['email'] ?? '');

        $usuario = $this->usuarioModel->buscarPorEmail($email);

        if (!$usuario) {

            $this->render->renderiza(
                'olvidePassword',
                [
                    'error' => 'No existe una cuenta asociada a ese correo electrónico.',

                    'login_show_url' =>
                        $this->appConfig['base_path_url']
                        . $this->appConfig['login_show_url'],

                    'enviar_recuperacion_url' =>
                        $this->appConfig['base_path_url']
                        . $this->appConfig['enviar_recuperacion_url']
                ]
            );

            return;
        }

        $token = bin2hex(random_bytes(32));

        $this->usuarioModel->guardarTokenRecuperacion(
            $usuario['id'],
            $token
        );

        $linkRecuperacion =
            $this->appConfig['base_url']
            . $this->appConfig['restablecer_password_url']
            . '?token='
            . $token;

        $mailEnviado =
            $this->mailService->enviarRecuperacionPassword(
                $usuario['email'],
                $usuario['nombre'],
                $linkRecuperacion
            );

        if (!$mailEnviado) {

            $this->render->renderiza(
                'olvidePassword',
                [
                    'error' =>
                        'No se pudo enviar el correo de recuperación. Intentá nuevamente.',

                    'login_show_url' =>
                        $this->appConfig['base_path_url']
                        . $this->appConfig['login_show_url'],

                    'enviar_recuperacion_url' =>
                        $this->appConfig['base_path_url']
                        . $this->appConfig['enviar_recuperacion_url']
                ]
            );

            return;
        }

        $this->render->renderiza(
            'olvidePassword',
            [
                'success' =>
                    'Te enviamos un correo con instrucciones para recuperar tu contraseña.',

                'login_show_url' =>
                    $this->appConfig['base_path_url']
                    . $this->appConfig['login_show_url'],

                'enviar_recuperacion_url' =>
                    $this->appConfig['base_path_url']
                    . $this->appConfig['enviar_recuperacion_url']
            ]
        );
    }

    public function restablecerPassword()
    {
        $token = trim($_GET['token'] ?? '');

        if ($token === '') {

            header(
                'Location:' .
                $this->appConfig['base_path_url'] .
                $this->appConfig['login_show_url']
            );

            exit();
        }

        $usuario = $this->usuarioModel
            ->buscarPorTokenRecuperacion(
                $token
            );

        if (!$usuario) {

            $this->render->renderiza(
                'restablecerPassword',
                [
                    'error' =>
                        'El enlace de recuperación no es válido o ya fue utilizado.',

                    'login_show_url' =>
                        $this->appConfig['base_path_url']
                        . $this->appConfig['login_show_url']
                ]
            );

            return;
        }

        $this->render->renderiza(
            'restablecerPassword',
            [
                'token' => $token,
                'login_show_url' => $this->appConfig['base_path_url'] . $this->appConfig['login_show_url'],
                'guardar_password_url' => $this->appConfig['base_path_url'] . $this->appConfig['guardar_password_url']
            ]
        );
    }

    public function guardarPassword(){

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

            header(
                'Location:' .
                $this->appConfig['base_path_url'] .
                $this->appConfig['login_show_url']
            );

            exit();
        }

        $token = trim($_POST['token'] ?? '');
        $password = $_POST['password'] ?? '';

        $confirmPassword =
            $_POST['confirm_password'] ?? '';
        $usuario =
            $this->usuarioModel
                ->buscarPorTokenRecuperacion(
                    $token
                );

        if (!$usuario) {

            $this->render->renderiza(
                'restablecerPassword',
                [
                    'error' =>
                        'El enlace de recuperación no es válido o ha expirado.',

                    'login_show_url' =>
                        $this->appConfig['base_path_url']
                        . $this->appConfig['login_show_url']
                ]
            );

            return;
        }
        if ($password !== $confirmPassword) {

            $this->render->renderiza(
                'restablecerPassword',
                [
                    'token' => $token,

                    'error' =>
                        'Las contraseñas ingresadas no coinciden.',

                    'guardar_password_url' =>
                        $this->appConfig['base_path_url']
                        . $this->appConfig['guardar_password_url'],

                    'login_show_url' =>
                        $this->appConfig['base_path_url']
                        . $this->appConfig['login_show_url']
                ]
            );
        }
        $passwordHash = password_hash(
            $password,
            PASSWORD_DEFAULT
        );

        $this->usuarioModel->actualizarPassword(
            $usuario['id'],
            $passwordHash
        );
        $this->usuarioModel->limpiarTokenRecuperacion($usuario['id']);
        header(
            'Location:' .
            $this->appConfig['base_path_url'] .
            $this->appConfig['login_show_url'] .
            '?password_reset=ok'
        );

        exit();
    }

}
