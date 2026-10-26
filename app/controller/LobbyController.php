<?php

namespace app\controller;

class LobbyController
{
    private $render;
    private $model;
    private $appConfig;

    public function __construct($render, $model, $appConfig)
    {
        $this->render = $render;
        $this->model = $model;
        $this->appConfig = $appConfig;
    }

    public function show()
    {
        if (!isset($_SESSION['usuario_id'])) {
            header('Location:' . $this->appConfig['base_path_url'] . $this->appConfig['login_show_url']);
            exit();
        }

        $usuario = $_SESSION['usuario'];
        $datos = $this->model->obtenerDatosLobby($usuario);
        $basePath = $this->appConfig['base_path_url'];

        $nivel = $datos['nivel'];
        $nivelRequerido = $datos['nivel_requerido_crear_pregunta'];

        foreach ($datos['ranking'] as $i => &$fila) {
            $fila['posicion'] = $i + 1;
            $fila['usuario_actual'] = $fila['usuario'] === $usuario;
            $fila['puntaje'] = $this->formatearNumero($fila['puntaje']);
        }
        unset($fila);

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
            'ranking' => $datos['ranking'],
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
        ]);
    }

    private function formatearNumero($numero)
    {
        return number_format($numero, 0, ',', '.');
    }
}
