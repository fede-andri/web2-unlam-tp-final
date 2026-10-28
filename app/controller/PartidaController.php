<?php

namespace app\controller;

use Request;

class PartidaController
{
    private const LETRAS = ['A', 'B', 'C', 'D', 'E', 'F'];
    private const COLOR_POR_DEFECTO = '#62d4b8';

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

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirigir($this->appConfig['lobby_url']);
        }

        if ($this->model->getPartidaEnCurso($usuarioId) === null) {
            $partidaId = $this->model->crearPartida($usuarioId);

            if (!$this->model->asignarSiguientePregunta($partidaId)) {
                // No hay preguntas activas cargadas
                $this->model->terminarPartida($partidaId);
                $this->redirigir($this->appConfig['lobby_url']);
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

        $pregunta = $this->model->getPregunta((int) $partida['pregunta_actual_id']);
        if ($pregunta === null) {
            $this->model->terminarPartida((int) $partida['id']);
            $this->redirigir('/partida/resultado?id=' . (int) $partida['id']);
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
        ]));
    }

   
    public function responder()
    {
        $usuarioId = $this->requiereLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirigir('/partida/jugar');
        }

        $partida = $this->model->getPartidaEnCurso($usuarioId);
        if ($partida === null || empty($partida['pregunta_actual_id'])) {
            $this->redirigir($this->appConfig['lobby_url']);
        }

        $partidaId = (int) $partida['id'];
        $respuestaId = (int) Request::post('respuesta_id', 0);

        $acerto = $this->model->registrarRespuesta(
            $partidaId,
            (int) $partida['pregunta_actual_id'],
            $respuestaId
        );

        // Respuesta inválida o repetida: se vuelve a mostrar la pregunta
        if ($acerto === null) {
            $this->redirigir('/partida/jugar');
        }

        if ($acerto) {
            $this->model->sumarPunto($partidaId);

            if ($this->model->asignarSiguientePregunta($partidaId)) {
                $this->redirigir('/partida/jugar');
            }
            // Respondio todas las preguntas disponibles: la partida termina igual
        }

        $this->model->terminarPartida($partidaId);
        $this->redirigir('/partida/resultado?id=' . $partidaId);
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

        $data = array_merge($this->datosHeader(), [
            'puntaje' => $puntaje,
            'singular' => $puntaje === 1,
            'perdio' => $detalle !== null,
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

    private function redirigir(string $ruta): void
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
}