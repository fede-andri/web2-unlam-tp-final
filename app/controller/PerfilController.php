<?php

namespace app\controller;

class PerfilController
{
    private $render;
    private $usuarioModel;
    private $appConfig;

    public function __construct($render, $usuarioModel, $appConfig)
    {
        $this->render = $render;
        $this->usuarioModel = $usuarioModel;
        $this->appConfig = $appConfig;
    }

    public function show()
    {
        if (!isset($_SESSION['usuario_id'])) {
            header('Location:' . $this->appConfig['base_path_url'] . $this->appConfig['login_show_url']);
            exit();
        }

        $usuario = $this->usuarioModel->buscarPorId($_SESSION['usuario_id']);

        if (!$usuario) {
            header('Location:' . $this->appConfig['base_path_url'] . $this->appConfig['logout_url']);
            exit();
        }

        $nombre = $usuario['nombre'] ?? '';
        $foto = !empty($usuario['foto']) ? $usuario['foto'] : 'default.png';

        $data = [
            // Datos del perfil
            'nombre'           => $nombre,
            'usuario'          => $usuario['usuario'] ?? '',
            'email'            => $usuario['email'] ?? '',
            'fecha_nacimiento' => $this->formatearFecha($usuario['fecha_nacimiento'] ?? ''),
            'sexo'             => $this->formatearSexo($usuario['sexo'] ?? ''),
            'pais'             => $usuario['pais'] ?? '',
            'ciudad'           => $usuario['ciudad'] ?? '',
            'iniciales'        => $this->iniciales($nombre),
            'foto_url'         => $this->appConfig['base_path_url'] . '/public/img/perfiles/' . $foto,
            'qr_url'           => $this->urlPerfilPublico($usuario['usuario'] ?? ''),
            'qr_alt'           => 'Código QR de tu perfil',
            'volver_url'       => $this->appConfig['base_path_url'] . $this->appConfig['lobby_url'],

            // Header (mismo patrón que RankingController)
            'base_url'         => $this->appConfig['base_path_url'],
            'base_path_url'    => $this->appConfig['base_path_url'],
            'mostrar_inicio'   => true,
            'mostrar_ranking'  => true,
            'ranking_show_url' => $this->appConfig['base_path_url'] . $this->appConfig['ranking_show_url'],
            'mostrar_perfil'   => true,
            'perfil_url'       => $this->appConfig['base_path_url'] . $this->appConfig['perfil_url'],
            'mostrar_logout'   => true,
            'logout_url'       => $this->appConfig['base_path_url'] . $this->appConfig['logout_url'],
        ];

        if (!empty($usuario['latitud']) && !empty($usuario['longitud'])) {
            $data['tiene_ubicacion'] = true;
            $data['latitud']  = $usuario['latitud'];
            $data['longitud'] = $usuario['longitud'];
        }

        $this->render->renderiza('perfil', $data);
    }

    public function ver()
    {
        if (!isset($_SESSION['usuario_id'])) {
            header('Location:' . $this->appConfig['base_path_url'] . $this->appConfig['login_show_url']);
            exit();
        }

        $nombreUsuario = trim($_GET['usuario'] ?? '');
        $jugador = $nombreUsuario === '' ? null : $this->usuarioModel->buscarPerfilPublico($nombreUsuario);

        $data = $this->datosHeader();

        if ($jugador === null) {
            $data['no_encontrado'] = true;
            $this->render->renderiza('perfilPublico', $data);
            return;
        }

        $foto = !empty($jugador['foto']) ? $jugador['foto'] : 'default.png';

        $this->render->renderiza('perfilPublico', array_merge($data, [
            'nombre'           => $jugador['nombre'],
            'usuario'          => $jugador['usuario'],
            'pais'             => $jugador['pais'],
            'ciudad'           => $jugador['ciudad'],
            'puntaje_total'    => (int) $jugador['puntaje_total'],
            'partidas_jugadas' => (int) $jugador['partidas_jugadas'],
            'foto_url'         => $this->appConfig['base_path_url'] . '/public/img/perfiles/' . $foto,
            'qr_url'           => $this->urlPerfilPublico($jugador['usuario']),
            'qr_alt'           => 'Código QR del perfil de ' . $jugador['usuario'],
        ]));
    }

    // Dirección absoluta que abre el QR; se arma con el host del pedido para que
    // funcione igual en local, en la red del celular y en el hosting.
    private function urlPerfilPublico($usuario)
    {
        $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
        $host = preg_match('/^[A-Za-z0-9.\-]+(:\d+)?$/', $_SERVER['HTTP_HOST'] ?? '')
            ? $_SERVER['HTTP_HOST']
            : 'localhost';

        return ($https ? 'https://' : 'http://') . $host
            . $this->appConfig['base_path_url'] . '/perfil/ver?usuario=' . rawurlencode($usuario);
    }

    private function datosHeader()
    {
        $base = $this->appConfig['base_path_url'];

        return [
            'base_url'         => $base,
            'mostrar_inicio'   => true,
            'mostrar_ranking'  => true,
            'ranking_show_url' => $base . $this->appConfig['ranking_show_url'],
            'mostrar_perfil'   => true,
            'perfil_url'       => $base . $this->appConfig['perfil_url'],
            'mostrar_logout'   => true,
            'logout_url'       => $base . $this->appConfig['logout_url'],
            'volver_url'       => $base . $this->appConfig['lobby_url'],
        ];
    }

    private function formatearFecha($fecha)
    {
        if ($fecha === '') {
            return '';
        }
        $d = \DateTime::createFromFormat('Y-m-d', $fecha);
        return $d ? $d->format('d/m/Y') : $fecha;
    }

    private function formatearSexo($valor)
    {
        $opciones = [
            'MASCULINO'         => 'Masculino',
            'FEMENINO'          => 'Femenino',
            'OTRO'              => 'Otro',
            'PREFIERO_NO_DECIR' => 'Prefiero no decir',
        ];
        return $opciones[$valor] ?? $valor;
    }

    private function iniciales($nombre)
    {
        $partes = preg_split('/\s+/', trim($nombre));
        $ini = '';
        foreach (array_slice($partes, 0, 2) as $p) {
            $ini .= mb_strtoupper(mb_substr($p, 0, 1));
        }
        return $ini;
    }
}