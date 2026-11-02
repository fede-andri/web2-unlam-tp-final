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
        $base      = $this->appConfig['base_path_url'];
        $usuarioId = (int) $_SESSION['usuario_id'];
        $ranking   = $this->model->getRanking();

        $estaEnLista = false;
        foreach ($ranking as $i => &$fila) {
            $fila['posicion']       = $i + 1;
            $fila['usuario_actual'] = ((int) $fila['id'] === $usuarioId);
            $fila['url_perfil']     = $this->urlPerfil($fila['usuario']);

            if ($fila['usuario_actual']) {
                $estaEnLista = true;
            }
        }
        unset($fila);

        // Si el usuario quedó fuera de la lista, se agrega su fila con la posición real
        $miPosicion = null;
        if (!$estaEnLista) {
            $miPosicion = $this->model->getPosicionDeUsuario($usuarioId);

            if ($miPosicion !== null) {
                $miPosicion['url_perfil'] = $this->urlPerfil($miPosicion['usuario']);
            }
        }

        $this->render->renderiza('ranking', [
            'ranking'          => $ranking,
            'hay_jugadores'    => count($ranking) > 0,
            'mi_posicion'      => $miPosicion,
            'base_url'         => $base,
            'ranking_show_url' => $base . $this->appConfig['ranking_show_url'],
            'mostrar_inicio'   => true,
            'mostrar_ranking'  => true,
            'mostrar_logout'   => true,
            'mostrar_perfil'   => true,
            'perfil_url'       => $base . $this->appConfig['perfil_url'],
            'logout_url'       => $base . $this->appConfig['logout_url'],
        ]);
    }

    private function urlPerfil($usuario)
    {
        return $this->appConfig['base_path_url'] . '/perfil/ver?usuario=' . rawurlencode($usuario);
    }

}