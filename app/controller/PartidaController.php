<?php

namespace app\controller;

use Redirect;

class PartidaController{

    private $model;
    private $render;

    public function __construct($model, $render){
        $this->model = $model;
        $this->render = $render;
    }

    public function getPartidas(){
        if(!isset($_SESSION['usuario_id'])){
            header('Location: /web2-unlam-tp-final/login/show');
            exit();
        }

        $partidas = $this->model->getPartidasDeUsuario((int)$_SESSION['usuario_id']);

        $this->render->renderiza('partidas', [
            'partidas' => $partidas,
            'cant_partidas' => count($partidas) > 0,
            'usuario' => $_SESSION['usuario']
        ]);
    }

}