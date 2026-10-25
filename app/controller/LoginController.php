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
        ]);
    }
    public function login()
    {
        $usuario  = trim(Request::post('usuario') ?? '');
        $password = Request::post('password') ?? '';

        $datosUsuario = $this->model->autenticar($usuario, $password);

        if ($datosUsuario === null) {
            Redirect::to('/login?login_error=1');
        }
//        session_regenerate_id(true);
        $_SESSION['usuario_id'] = $datosUsuario['id'];
        $_SESSION['usuario'] = $datosUsuario['usuario'];
        Redirect::toIndex();
    }

    public function logout()
    {
        $_SESSION = [];
        session_destroy();
        Redirect::toIndex();
    }
}
