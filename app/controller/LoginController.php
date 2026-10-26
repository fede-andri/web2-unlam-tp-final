<?php

namespace app\controller;

use Redirect;
use Request;

class LoginController
{
    private $model;
    private $render;
    private $appConfig;
    public function __construct($model, $render, $appConfig)
    {
        $this->model = $model;
        $this->render = $render;
        $this->appConfig = $appConfig;
    }

    public function show()
    {
        if (isset($_SESSION['usuario_id'])) {
            Redirect::toIndex();
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
}
