<?php

namespace app\controller;

use Redirect;

class RankingController{

    private $model;
    private $render;
    private $appConfig;

    public function __construct($render, $model, $appConfig){
        $this->render = $render;
        $this->model = $model;
        $this->appConfig = $appConfig;
    }

    public function show(){
        if(!isset($_SESSION['usuario_id'])){
            header('Location:' . $this->appConfig['base_path_url'] .$this->appConfig['login_show_url']);
            exit();
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
            'base_url'=>$this->appConfig['base_path_url'],
            'ranking_show_url' => $this->appConfig['base_path_url'] . $this->appConfig['ranking_show_url'],
            'mostrar_inicio'=>true,
            'mostrar_ranking'=>true,
        ]);
    }

}