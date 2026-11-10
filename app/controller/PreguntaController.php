<?php

namespace app\controller;

use Request;

class PreguntaController
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
        $usuarioId = $this->requiereLogin();
        $categorias = $this->model->getCategorias();

        $error = Request::get('error');
        $success = Request::get('success');

        $this->render->renderiza('sugerirPregunta', array_merge($this->datosHeader(), [
            'categorias' => $categorias,
            'error' => $error ? urldecode($error) : null,
            'success' => $success ? urldecode($success) : null,
            'guardar_pregunta_url' => $this->appConfig['base_path_url'] . $this->appConfig['guardar_pregunta_url'],
            'lobby_url' => $this->appConfig['base_path_url'] . $this->appConfig['lobby_url'],
        ]));
    }

    public function guardar()
    {
        $usuarioId = $this->requiereLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirigir($this->appConfig['sugerir_pregunta_url']);
        }

        $categoriaId = (int) Request::post('categoria_id', 0);
        $enunciado = trim(Request::post('enunciado', ''));
        $respuestasTexto = $_POST['respuestas'] ?? [];
        $correctaIndex = (int) Request::post('correcta', -1);

        if ($categoriaId <= 0 || empty($enunciado)) {
            $this->redirigir($this->appConfig['sugerir_pregunta_url'] . '?error=' . urlencode('Completá la categoría y el enunciado.'));
        }

        if (count($respuestasTexto) < 2 || $correctaIndex < 0 || $correctaIndex >= count($respuestasTexto)) {
            $this->redirigir($this->appConfig['sugerir_pregunta_url'] . '?error=' . urlencode('Debes ingresar al menos 2 respuestas y marcar cuál es la correcta.'));
        }

        $respuestas = [];
        foreach ($respuestasTexto as $i => $texto) {
            $textoTrim = trim($texto);
            if ($textoTrim === '') {
                continue;
            }
            $respuestas[] = [
                'texto' => $textoTrim,
                'es_correcta' => ($i === $correctaIndex)
            ];
        }

        if (count($respuestas) < 2) {
            $this->redirigir($this->appConfig['sugerir_pregunta_url'] . '?error=' . urlencode('Las respuestas no pueden estar vacías.'));
        }

        $hasCorrect = false;
        foreach ($respuestas as $r) {
            if ($r['es_correcta']) {
                $hasCorrect = true;
                break;
            }
        }

        if (!$hasCorrect) {
            $this->redirigir($this->appConfig['sugerir_pregunta_url'] . '?error=' . urlencode('Debe haber al menos una respuesta correcta marcada.'));
        }

        $this->model->sugerirPregunta($categoriaId, $enunciado, $respuestas);

        $this->redirigir($this->appConfig['sugerir_pregunta_url'] . '?success=' . urlencode('¡Pregunta sugerida con éxito! Quedará pendiente de aprobación.'));
    }

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
            'mostrar_perfil' => true,
            'perfil_url'     => $base . $this->appConfig['perfil_url'],
        ];
    }
}
