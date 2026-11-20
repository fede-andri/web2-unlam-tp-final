<?php

namespace app\controller;

use app\model\PartidaModel;
use Request;

class PartidaController
{
    const LETRAS = ['A', 'B', 'C', 'D', 'E', 'F'];
    const COLOR_POR_DEFECTO = '#62d4b8';

    private $render;
    private $model;
    private $appConfig;

    public function __construct($render, $model, $appConfig)
    {
        $this->render = $render;
        $this->model = $model;
        $this->appConfig = $appConfig;
    }

    public function iniciar()
    {
        $usuarioId = $this->requiereLogin();

        $this->soloPost();

        // La partida se crea recien cuando el jugador elige categoria (confirmarCategoria).
        // Si ya tiene una en curso, la retoma; si no, pasa por la ruleta.
        if ($this->model->getPartidaEnCurso($usuarioId) !== null) {
            $this->redirigir('/partida/jugar');
        }

        $this->redirigir('/partida/ruleta');
    }

    public function ruleta()
    {
        $this->requiereLogin();

        $categorias = $this->model->getCategorias();

        $this->render->renderiza(
            'ruleta',
            array_merge(
                $this->datosHeader(),
                [
                    'categorias_json' => json_encode($categorias),
                    'confirmar_categoria_url' =>
                        $this->appConfig['base_path_url']
                        . '/partida/confirmarCategoria'
                ]
            )
        );
    }

    public function confirmarCategoria()
    {
        $usuarioId = $this->requiereLogin();

        $categoriaId = (int) Request::post('categoria_id', 0);

        // La categoria tiene que existir
        if ($categoriaId <= 0 || $this->model->getCategoriaPorId($categoriaId) === null) {
            $this->redirigir('/partida/ruleta');
        }

        if ($this->model->getPartidaEnCurso($usuarioId) === null) {
            $partidaId = $this->model->crearPartida($usuarioId, $categoriaId);

            if (!$this->model->asignarSiguientePregunta($partidaId)) {
                // No quedan preguntas nuevas en esa categoria: la partida vacia no se guarda
                $this->model->cancelarPartida($partidaId);
                $this->redirigir('/partida/sinPreguntas');
            }
        }

        $this->redirigir('/partida/jugar');
    }

    public function jugar()
    {
        $usuarioId = $this->requiereLogin();

        $partida = $this->model->getPartidaEnCurso($usuarioId);
        if ($partida === null || empty($partida['pregunta_actual_id'])) {
            $this->redirigir($this->appConfig['lobby_url']);
        }

        $partidaId = (int) $partida['id'];

        $this->terminarPartidaPorTiempo($partidaId);

        $pregunta = $this->model->getPregunta((int) $partida['pregunta_actual_id']);
        if ($pregunta === null) {
            $this->model->terminarPartida($partidaId);
            $this->irAResultado($partidaId);
        }

        //partida en curso que no tenia temp, se lo arranca ahora
        $restantes = $this->model->segundosRestantes($partidaId);
        if ($restantes === null) {
            $this->model->iniciarTemporizador($partidaId);
            $restantes = PartidaModel::SEGUNDOS_POR_PREGUNTA;
        }
        // Se mezclan las opciones y se les asigna la letra A, B, C, D
        $respuestas = $pregunta['respuestas'];
        shuffle($respuestas);

        $opciones = [];
        foreach ($respuestas as $i => $respuesta) {
            $opciones[] = [
                'letra' => self::LETRAS[$i] ?? '',
                'id' => (int) $respuesta['id'],
                'texto' => $respuesta['texto'],
            ];
        }

        $this->render->renderiza('partida', array_merge($this->datosHeader(), [
            'categoria' => $pregunta['categoria'],
            'categoria_color' => $this->colorValido($pregunta['color']),
            'enunciado' => $pregunta['enunciado'],
            'opciones' => $opciones,
            'puntaje' => (int) $partida['puntaje'],
            'numero_pregunta' => (int) $partida['puntaje'] + 1,
            'responder_url' => $this->appConfig['base_path_url'] . '/partida/responder',
            'tiempo_url' => $this->appConfig['base_path_url'] . '/partida/tiempoAgotado',
            'segundos_totales' => PartidaModel::SEGUNDOS_POR_PREGUNTA,
            'segundos_restantes' => $restantes,
            'reportar_url' => $this->appConfig['base_path_url'] . '/partida/reportar',
            'reportada' => Request::get('reportada') !== null,
        ]));
    }

    public function reportar()
    {
        $usuarioId = $this->requiereLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirigir('/partida/jugar');
        }

        $partida = $this->model->getPartidaEnCurso($usuarioId);
        if ($partida === null || empty($partida['pregunta_actual_id'])) {
            $this->redirigir($this->appConfig['lobby_url']);
        }

        $preguntaId = (int) $partida['pregunta_actual_id'];
        $motivo = trim(Request::post('motivo', 'Pregunta reportada por el usuario'));
        if (empty($motivo)) {
            $motivo = 'Pregunta inválida';
        }

        $this->model->reportarPregunta($preguntaId, $usuarioId, $motivo);

        $reemplazada = $this->model->reemplazarPreguntaReportada((int) $partida['id'], $preguntaId);

        if (!$reemplazada) {
            $this->model->terminarPartida((int) $partida['id']);
            $this->redirigir('/partida/resultado?id=' . (int) $partida['id']);
        }

        $this->redirigir('/partida/jugar?reportada=1');
    }


    public function responder()
    {
        $usuarioId = $this->requiereLogin();

        $this->soloPost();

        $partida = $this->model->getPartidaEnCurso($usuarioId);
        if ($partida === null || empty($partida['pregunta_actual_id'])) {
            $this->redirigir($this->appConfig['lobby_url']);
        }

        $partidaId = (int) $partida['id'];
        $this->terminarPartidaPorTiempo($partidaId);

        $respuestaId = (int) Request::post('respuesta_id', 0);

        $acerto = $this->model->registrarRespuesta(
            $partidaId,
            (int) $partida['pregunta_actual_id'],
            $respuestaId
        );

        // Respuesta inválida se vuelve a mostrar la pregunta
        if ($acerto === null) {
            $this->redirigir('/partida/jugar');
        }

        if ($acerto) {
            $this->model->sumarPunto($partidaId);

            if ($this->model->asignarSiguientePregunta($partidaId)) {
                $this->redirigir('/partida/jugar');
            }
        }

        $this->model->terminarPartida($partidaId);
        $this->irAResultado($partidaId);
    }
    public function tiempoAgotado()
    {
        $usuarioId = $this->requiereLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirigir('/partida/jugar');
        }

        $partida = $this->model->getPartidaEnCurso($usuarioId);
        if ($partida === null) {
            $this->redirigir($this->appConfig['lobby_url']);
        }

        $partidaId = (int) $partida['id'];

        $this->terminarPartidaPorTiempo($partidaId);

        // Todavía no venció de verdad (el reloj del navegador iba adelantado)
        $this->redirigir('/partida/jugar');
    }

    // cuando no quedan mas preguntas para el jugador
    public function sinPreguntas()
    {
        $usuarioId = $this->requiereLogin();

        $estadisticas = $this->getEstadisticasFomateadas($usuarioId);

        $this->render->renderiza('partidaResultado', array_merge($this->datosHeader(), [
            'sin_preguntas' => true,
            'estadisticas' => $estadisticas,
            'categoria_color' => self::COLOR_POR_DEFECTO,
            'lobby_url' => $this->appConfig['base_path_url'] . $this->appConfig['lobby_url'],
        ]));
    }

    public function resultado()
    {
        $usuarioId = $this->requiereLogin();

        $partida = $this->model->getPartida((int) Request::get('id', 0), $usuarioId);
        if ($partida === null || $partida['estado'] !== 'TERMINADA') {
            $this->redirigir($this->appConfig['lobby_url']);
        }

        $detalle = $this->model->getDetalleError((int) $partida['id']);
        $puntaje = (int) $partida['puntaje'];
        $tiempoAgotado = ($partida['motivo_fin'] ?? null) === 'TIEMPO';

        $estadisticas = $this->getEstadisticasFomateadas($usuarioId);

        $data = array_merge($this->datosHeader(), [
            'puntaje' => $puntaje,
            'singular' => $puntaje === 1,
            'perdio' => $detalle !== null || $tiempoAgotado,
            'tiempo_agotado' => $tiempoAgotado,
            'estadisticas' => $estadisticas,
            'jugar_url' => $this->appConfig['base_path_url'] . '/partida/iniciar',
            'lobby_url' => $this->appConfig['base_path_url'] . $this->appConfig['lobby_url'],
            'categoria_color' => self::COLOR_POR_DEFECTO,
        ]);

        if ($detalle !== null) {
            $data['categoria'] = $detalle['categoria'];
            $data['categoria_color'] = $this->colorValido($detalle['color']);
            $data['enunciado'] = $detalle['enunciado'];
            $data['elegida'] = $detalle['elegida'];
            $data['correcta'] = $detalle['correcta'];
        }

        $this->render->renderiza('partidaResultado', $data);
    }

    public function getPartidas()
    {
        $usuarioId = $this->requiereLogin();

        $partidas = $this->model->getPartidasDeUsuario($usuarioId);

        $this->render->renderiza('partidas', array_merge($this->datosHeader(), [
            'partidas' => $partidas,
            'cant_partidas' => count($partidas) > 0,
            'usuario' => $_SESSION['usuario'],
        ]));
    }

    //helpers
    private function requiereLogin(): int
    {
        if (!isset($_SESSION['usuario_id'])) {
            $this->redirigir($this->appConfig['login_show_url']);
        }
        return (int) $_SESSION['usuario_id'];
    }

    private function redirigir(string $ruta)
    {
        header('Location: ' . $this->appConfig['base_path_url'] . $ruta);
        exit();
    }

    private function datosHeader(): array
    {
        $base = $this->appConfig['base_path_url'];

        return [
            'home_url' => $base . $this->appConfig['lobby_url'],
            'base_url' => $base,
            'ranking_show_url' => $base . $this->appConfig['ranking_show_url'],
            'logout_url' => $base . $this->appConfig['logout_url'],
            'mostrar_ranking' => true,
            'mostrar_logout' => true,
        ];
    }
    private function colorValido($color): string
    {
        return preg_match('/^#[0-9a-fA-F]{6}$/', (string) $color) ? $color : self::COLOR_POR_DEFECTO;
    }

    private function terminarPartidaPorTiempo(int $partidaId)
    {
        if ($this->model->tiempoAgotado($partidaId)) {
            $this->model->terminarPorTiempo($partidaId);
            $this->irAResultado($partidaId);
        }
    }

    public function getEstadisticasFomateadas(int $usuarioId)
    {
        $estadisticas = $this->model->getEstadisticas($usuarioId);
        $estadisticas['puntaje_total'] = number_format($estadisticas['puntaje_total'], 0, ',', '.');
        return $estadisticas;
    }

    public function soloPost()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirigir($this->appConfig['lobby_url']);
        }
    }
    public function irAResultado(int $partidaId)
    {
        $this->redirigir('/partida/resultado?id=' . $partidaId);
    }
}