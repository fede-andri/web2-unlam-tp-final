<?php

namespace app\controller;

use Redirect;

class RankingController{

    private $model;
    private $render;

    public function __construct($render, $model){
        $this->render = $render;
        $this->model = $model;
    }

    public function show(){
        if(!isset($_SESSION['usuario_id'])){
            Redirect::to('/login');
        }
        $ranking = $this->model->getRanking();

        foreach ($ranking as $i => &$fila){
            $fila['posicion'] = $i + 1;
            $fila['usuario_actual'] = ((int) $fila['id'] === (int) $_SESSION['usuario_id']);
        }
        unset($fila);

        $this->render->renderiza('ranking', [
            'ranking' => $ranking,
            'hay_jugadores' => count($ranking) > 0,
        ]);
    }

}