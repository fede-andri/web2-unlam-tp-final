<?php
/*
namespace app\controller;

use Redirect;

class HomeController
{
    private $render;
    private $equipoModel;
    private $appConfig;
    public function __construct($render, $equipoModel, $appConfig){
        $this->render = $render;
        $this->equipoModel = $equipoModel;
        $this->appConfig = $appConfig;
    }

    public function show(){
        if (!isset($_SESSION['usuario_id'])) {
            header('Location:' . $this->appConfig['base_path_url'] .$this->appConfig['login_show_url']);
            exit();
        }
        $datosEquipo = $this->equipoModel->obtenerDatosEquipo();
        $data = [
            'nombre' => $datosEquipo['nombre'],
            'universidad' => $datosEquipo['universidad'],
            'materia' => $datosEquipo['materia'],
            'anio' => $datosEquipo['anio'],
            'integrantes' => $datosEquipo['integrantes'],
            'logout_url' => $this->appConfig['base_path_url'] . $this->appConfig['logout_url'],
            'ranking_show_url' => $this->appConfig['base_path_url'] . $this->appConfig['ranking_show_url'],
            'home_url' => $this->appConfig['base_path_url']
        ];
        $this->render->renderiza('presentacion', $data);
    }
}
*/