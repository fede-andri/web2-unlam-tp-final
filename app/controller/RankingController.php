<?php

namespace app\controller;

use app\model\RankingModel;
use Redirect;
use Request;

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
        $periodo = Request::get('periodo', RankingModel::PERIODO_POR_DEFECTO);
        if (!RankingModel::esPeriodoValido($periodo)) {
            $periodo = RankingModel::PERIODO_POR_DEFECTO;
        }

        $ranking   = $this->model->getRanking(20, $periodo);

        $anteriores = $this->model->getPosicionesAnteriores($periodo);

        $estaEnLista = false;
        foreach ($ranking as $i => &$fila) {
            $fila['posicion']       = $i + 1;
            $fila['usuario_actual'] = ((int) $fila['id'] === $usuarioId);
            $fila['url_perfil']     = $this->urlPerfil($fila['usuario']);
            $fila = array_merge($fila, $this->movimiento((int) $fila['id'], $fila['posicion'], $anteriores));

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

        $periodos = [];
        foreach (RankingModel::PERIODOS as $clave => $datos) {
            $periodos[] = [
                'nombre' => $datos['nombre'],
                'url'    => $base . $this->appConfig['ranking_show_url'] . '?periodo=' . $clave,
                'activo' => $clave === $periodo,
            ];
        }
        $this->render->renderiza('ranking', [
            'ranking'          => $ranking,
            'hay_jugadores'    => count($ranking) > 0,
            'mi_posicion'      => $miPosicion,
            'periodos'         => $periodos,
            'periodo_etiqueta' => RankingModel::PERIODOS[$periodo]['etiqueta'],
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

    private function movimiento(int $usuarioId, int $posicionActual, array $anteriores): array
    {
        if (!isset($anteriores[$usuarioId])) {
            return ['mov_sube' => false, 'mov_baja' => false, 'mov_nuevo' => !empty($anteriores), 'mov_cantidad' => 0];
        }

        $cambio = $anteriores[$usuarioId] - $posicionActual; // positivo = subio
        return [
            'mov_sube'     => $cambio > 0,
            'mov_baja'     => $cambio < 0,
            'mov_nuevo'    => false,
            'mov_cantidad' => abs($cambio),
        ];
    }
    private function urlPerfil($usuario)
    {
        return $this->appConfig['base_path_url'] . '/perfil/ver?usuario=' . rawurlencode($usuario);
    }

}