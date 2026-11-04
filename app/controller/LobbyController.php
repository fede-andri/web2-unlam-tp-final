<?php

namespace app\controller;

class LobbyController
{
    private $render;
    private $model;
    private $appConfig;
    private $rankingModel;

    public function __construct($render, $model, $appConfig, $rankingModel)
    {
        $this->render = $render;
        $this->model = $model;
        $this->appConfig = $appConfig;
        $this->rankingModel = $rankingModel;
    }

    public function show()
    {
        if (!isset($_SESSION['usuario_id'])) {
            header('Location:' . $this->appConfig['base_path_url'] . $this->appConfig['login_show_url']);
            exit();
        }

        $usuario = $_SESSION['usuario'];
        $usuarioIdActual = (int) $_SESSION['usuario_id'];
        $datos = $this->model->obtenerDatosLobby($usuario);
        $basePath = $this->appConfig['base_path_url'];

        $nivel = $datos['nivel'];
        $nivelRequerido = $datos['nivel_requerido_crear_pregunta'];
        $ranking = $this->rankingModel->getRanking(5);
        $estaEnTop = false;

        foreach ($ranking as $i => &$fila) {
            $fila['posicion'] = $i + 1;
            $fila['usuario_actual'] = ((int) $fila['id'] === $usuarioIdActual);;
            $fila['puntaje'] = $this->formatearNumero($fila['puntaje_total']);
            $fila['url_perfil'] = $this->urlPerfil($fila['usuario']);

            if ($fila['usuario_actual']) {
                $estaEnTop = true;
            }
        }
        unset($fila);

        $miPosicion = null;
        if (!$estaEnTop) {
            $miPosicion = $this->rankingModel->getPosicionDeUsuario($usuarioIdActual);

            if ($miPosicion !== null) {
                $miPosicion['puntaje'] = $this->formatearNumero($miPosicion['puntaje_total']);
                $miPosicion['url_perfil'] = $this->urlPerfil($miPosicion['usuario']);
            }
        }

        foreach ($datos['ultimas_partidas'] as &$partida) {
            $partida['puntaje'] = $this->formatearNumero($partida['puntaje']);
        }
        unset($partida);

        $estadisticas = $datos['estadisticas'];
        $estadisticas['puntaje_total'] = $this->formatearNumero($estadisticas['puntaje_total']);

        $this->render->renderiza('lobby', [
            'usuario' => $usuario,
            'nivel' => $nivel,
            'rango' => $datos['rango'],
            'xp_actual' => $this->formatearNumero($datos['xp_actual']),
            'xp_siguiente_nivel' => $this->formatearNumero($datos['xp_siguiente_nivel']),
            'xp_porcentaje' => (int) round($datos['xp_actual'] / $datos['xp_siguiente_nivel'] * 100),
            'estadisticas' => $estadisticas,
            'categorias' => $datos['categorias'],
            'ultimas_partidas' => $datos['ultimas_partidas'],
            'ranking' => $ranking,
            'mi_posicion' => $miPosicion,
            'crear_pregunta_bloqueada' => $nivel < $nivelRequerido,
            'nivel_requerido_crear_pregunta' => $nivelRequerido,
            'niveles_faltantes' => max($nivelRequerido - $nivel, 0),
            'progreso_desbloqueo' => (int) min(round($nivel / $nivelRequerido * 100), 100),

            // Header
            'home_url' => $basePath . $this->appConfig['lobby_url'],
            'base_url' => $basePath,
            'ranking_show_url' => $basePath . $this->appConfig['ranking_show_url'],
            'logout_url' => $basePath . $this->appConfig['logout_url'],
            'mostrar_ranking' => true,
            'mostrar_logout' => true,
            'mostrar_perfil' => true,
            'perfil_url'     => $this->appConfig['base_path_url'] . $this->appConfig['perfil_url'],
            'sugerir_pregunta_url' => $basePath . $this->appConfig['sugerir_pregunta_url'],
        ]);
    }

    private function urlPerfil($usuario)
    {
        return $this->appConfig['base_path_url'] . '/perfil/ver?usuario=' . rawurlencode($usuario);
    }
    private function formatearNumero($numero)
    {
        return number_format($numero, 0, ',', '.');
    }
}
