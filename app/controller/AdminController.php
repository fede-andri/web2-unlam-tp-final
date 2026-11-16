<?php

namespace app\controller;

class AdminController
{
    private $render;
    private $usuarioModel;
    private $appConfig;

    public function __construct(
        $render,
        $usuarioModel,
        $appConfig
    )
    {
        $this->render = $render;
        $this->usuarioModel = $usuarioModel;
        $this->appConfig = $appConfig;
    }

    public function show()
    {
        if (!isset($_SESSION['usuario_id'])) {
            header(
                'Location:' .
                $this->appConfig['base_path_url'] .
                $this->appConfig['login_show_url']
            );
            exit();
        }
        $usuario = $this->usuarioModel->buscarPorId($_SESSION['usuario_id']);
        if (!$usuario || $usuario['rol'] !== 'ADMIN') {
            header(
                'Location:' .
                $this->appConfig['base_path_url'] .
                $this->appConfig['lobby_url']
            );

            exit();
        }
        $this->render->renderiza(
            'admin',
            [
                'base_url' => $this->appConfig['base_path_url'],
                'mostrar_logout' => true,

                'logout_url' =>
                    $this->appConfig['base_path_url']
                    . $this->appConfig['logout_url'],

                'es_admin' => true,

                'admin_url' =>
                    $this->appConfig['base_path_url']
                    . $this->appConfig['admin_show_url']
            ]
        );
    }
}