<?php

namespace app\controller;

use Redirect;

class HomeController
{
    private $render;
    private $equipoModel;
    public function __construct($render, $equipoModel){
        $this->render = $render;
        $this->equipoModel = $equipoModel;
    }

    public function show(){
        if (!isset($_SESSION['usuario_id'])) {
            Redirect::to('/login');
        }
        $data = $this->equipoModel->obtenerDatosEquipo();
        $this->render->renderiza('presentacion', $data);
    }
}