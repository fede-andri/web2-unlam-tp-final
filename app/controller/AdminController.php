<?php

namespace app\controller;

use Request;

class AdminController
{
    private $render;
    private $usuarioModel;
    private $appConfig;
    //private $preguntaModel;
    private $adminModel;

    public function __construct(
        $render,
        $usuarioModel,
        $appConfig,
        //$preguntaModel = null
        $adminModel
    )
    {
        $this->render = $render;
        $this->usuarioModel = $usuarioModel;
        $this->appConfig = $appConfig;
        $this->adminModel = $adminModel;
        //$this->preguntaModel = $preguntaModel;
    }

    public function show()
    {
        $this->requiereAdmin();

        //$cantidadPendientes = $this->preguntaModel ? $this->preguntaModel->contarPreguntasPendientes() : 0;

        $this->render->renderiza(
            'admin',
            $this->datosAdminBase()
        );
    }

    public function metricas()
    {
        $this->render->renderiza(
            'adminMetricas',
            [
                'jugadores' => $this->adminModel->contarJugadores(),
                'partidas' => $this->adminModel->contarPartidas(),
                'preguntas_creadas' => $this->adminModel->contarPreguntasCreadas(),
                'usuarios_nuevos' => $this->adminModel->contarUsuariosNuevosHoy(),
                'base_url' => $this->appConfig['base_path_url'],
                'mostrar_logout' => true,
                'logout_url' => $this->appConfig['base_path_url'] . $this->appConfig['logout_url'],
                'admin_url' => $this->appConfig['base_path_url'] . $this->appConfig['admin_show_url']
            ]
        );
    }

    //Va a Editor
//    public function sugerencias()
//    {
//        $this->requiereAdmin();
//
//        $preguntas = $this->preguntaModel ? $this->preguntaModel->getPreguntasPendientes() : [];
//        $base = $this->appConfig['base_path_url'];
//        $success = Request::get('success');
//        $error = Request::get('error');
//
//        $this->render->renderiza(
//            'adminSugerencias',
//            array_merge($this->datosAdminBase(), [
//                'preguntas' => $preguntas,
//                'hay_preguntas' => count($preguntas) > 0,
//                'aprobar_url' => $base . '/admin/aprobarSugerencia',
//                'rechazar_url' => $base . '/admin/rechazarSugerencia',
//                'success' => $success ? urldecode($success) : null,
//                'error' => $error ? urldecode($error) : null,
//            ])
//        );
//    }

//    public function aprobarSugerencia()
//    {
//        $this->procesarEstadoSugerencia('ACTIVA', 'La pregunta sugerida fue aprobada y ya está activa en el juego.');
//    }
//
//    public function rechazarSugerencia()
//    {
//        $this->procesarEstadoSugerencia('BAJA', 'La pregunta sugerida fue rechazada y dada de baja.');
//    }
//
//    private function procesarEstadoSugerencia(string $estado, string $mensajeOk): void
//    {
//        $this->requiereAdmin();
//
//        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
//            $this->redirigir('/admin/sugerencias');
//        }
//
//        $preguntaId = (int) Request::post('pregunta_id', 0);
//        if ($preguntaId <= 0 || !$this->preguntaModel) {
//            $this->redirigir('/admin/sugerencias?error=' . urlencode('Pregunta inválida.'));
//        }
//
//        $this->preguntaModel->cambiarEstadoPregunta($preguntaId, $estado);
//        $this->redirigir('/admin/sugerencias?success=' . urlencode($mensajeOk));
//    }

    private function requiereAdmin(): array
    {
        if (!isset($_SESSION['usuario_id'])) {
            $this->redirigir($this->appConfig['login_show_url']);
        }

        $usuario = $this->usuarioModel->buscarPorId($_SESSION['usuario_id']);
        if (!$usuario || $usuario['rol'] !== 'ADMIN') {
            $this->redirigir($this->appConfig['lobby_url']);
        }

        return $usuario;
    }

    private function datosAdminBase(): array
    {
        $base = $this->appConfig['base_path_url'];

        return [
            'base_url' => $base,
            'home_url' => $base . $this->appConfig['admin_show_url'],
            'mostrar_logout' => true,
            'logout_url' => $base . $this->appConfig['logout_url'],
            'es_admin' => true,
            'admin_url' => $base . $this->appConfig['admin_show_url'],
            'sugerencias_url' => $base . '/admin/sugerencias',
            'metricas_url' => $this->appConfig['base_path_url']. $this->appConfig['metricas_url'],
            'estadisticas_url' => $this->appConfig['base_path_url']. $this->appConfig['estadisticas_url'],
            'gestion_url' => $this->appConfig['base_path_url'] . $this->appConfig['gestion_url'] ,
        ];
    }

    private function redirigir(string $ruta): void
    {
        header('Location: ' . $this->appConfig['base_path_url'] . $ruta);
        exit();
    }
}
