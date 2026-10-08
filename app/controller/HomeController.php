<?php

namespace app\controller;

class HomeController
{
    private $render;
    private $equipoModel;
    public function __construct($render, $equipoModel){
        $this->render = $render;
        $this->equipoModel = $equipoModel;
    }

    public function show(){
        $data = $this->equipoModel->obtenerDatosEquipo();
        $this->render->renderiza('presentacion', $data);
    }
}