<?php

namespace app\controller;
require_once __DIR__ . '/../../helper/FotoPerfil.php';

use \FotoPerfil;
class PerfilController
{
    private $render;
    private $usuarioModel;
    private $appConfig;
    private $mapConfig;

    public function __construct($render, $usuarioModel, $appConfig, $mapConfig)
    {
        $this->render = $render;
        $this->usuarioModel = $usuarioModel;
        $this->appConfig = $appConfig;
        $this->mapConfig = $mapConfig;
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

        $base = $this->appConfig['base_path_url'];

        $nombre = $usuario['nombre'] ?? '';
        $foto = !empty($usuario['foto']) ? $usuario['foto'] : 'default.png';

        $stats = $this->usuarioModel->buscarPerfilPublico($usuario['usuario'] ?? '');

        $data = [
            // Datos del perfil
            'nombre'           => $nombre,
            'usuario'          => $usuario['usuario'] ?? '',
            'email'            => $usuario['email'] ?? '',
            'fecha_nacimiento' => $this->formatearFecha($usuario['fecha_nacimiento'] ?? ''),
            'sexo'             => $this->formatearSexo($usuario['sexo'] ?? ''),
            'pais'             => $usuario['pais'] ?? '',
            'ciudad'           => $usuario['ciudad'] ?? '',
            'puntaje_total'    => (int) ($stats['puntaje_total'] ?? 0),
            'partidas_jugadas' => (int) ($stats['partidas_jugadas'] ?? 0),
            'iniciales'        => $this->iniciales($nombre),
            'foto_url'         => $this->appConfig['base_path_url'] . '/public/img/perfiles/' . $foto,
            'qr_url'           => $this->urlPerfilPublico($usuario['usuario'] ?? ''),
            'qr_alt'           => 'Código QR de tu perfil',
            'volver_url' => $this->appConfig['base_path_url'] . $this->appConfig['lobby_url'],
            'editar_url' => $base . '/perfil/editar',
            'eliminar_cuenta_url' => $base . '/perfil/eliminarCuenta',

            // foto de perfil (cambiar / eliminar)
            'tiene_foto'        => $foto !== \FotoPerfil::POR_DEFECTO,
            'subir_foto_url'    => $base . '/perfil/subirFoto',
            'eliminar_foto_url' => $base . '/perfil/eliminarFoto',
            //mensajes de resultado (llegan por ?foto=... o ?foto_error=...)
            'success' => $this->mensajeExito($_GET['foto'] ?? '') ?? $this->mensajePerfil($_GET['perfil'] ?? ''),
            'error' => $this->mensajeError($_GET['foto_error'] ?? '') ?? $this->mensajeCuenta($_GET['cuenta_error'] ?? ''),

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


    public function subirFoto()
    {
        $this->exigirSesion();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->volverAlPerfil();
        }

        $resultado = \FotoPerfil::guardar($_FILES['foto'] ?? null);

        if ($resultado['error'] !== null) {
            $this->volverAlPerfil('?foto_error=' . $resultado['error']);
        }

        if ($resultado['foto'] === null) {
            $this->volverAlPerfil('?foto_error=vacio');
        }

        $usuario = $this->usuarioModel->buscarPorId($_SESSION['usuario_id']);

        $this->usuarioModel->actualizarFoto($_SESSION['usuario_id'], $resultado['foto']);

        \FotoPerfil::eliminar($usuario['foto'] ?? null);

        $this->volverAlPerfil('?foto=ok');
    }

    public function eliminarFoto()
    {
        $this->exigirSesion();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->volverAlPerfil();
        }

        $usuario = $this->usuarioModel->buscarPorId($_SESSION['usuario_id']);

        \FotoPerfil::eliminar($usuario['foto'] ?? null);
        $this->usuarioModel->actualizarFoto($_SESSION['usuario_id'], \FotoPerfil::POR_DEFECTO);

        $this->volverAlPerfil('?foto=eliminada');
    }

    // POST /perfil/eliminarCuenta
    public function eliminarCuenta()
    {
        $this->exigirSesion();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->volverAlPerfil();
        }

        $id = $_SESSION['usuario_id'];
        $usuario = $this->usuarioModel->buscarPorId($id);

        if (!$usuario) {
            header('Location:' . $this->appConfig['base_path_url'] . $this->appConfig['logout_url']);
            exit();
        }

        // Pedir la contraseña actual para confirmar
        $password = $_POST['password_confirmacion'] ?? '';

        if ($password === '' || !password_verify($password, $usuario['password'])) {
            $this->volverAlPerfil('?cuenta_error=password');
        }

        // Borrar la foto del disco y los datos de la base
        \FotoPerfil::eliminar($usuario['foto'] ?? null);
        $this->usuarioModel->eliminarCuenta($id);

        // Cerrar la sesión igual que el logout
        $_SESSION = [];
        session_destroy();

        header('Location:' . $this->appConfig['base_path_url'] . $this->appConfig['login_show_url']);
        exit();
    }

    private function mensajeCuenta($codigo)
    {
        $mensajes = [
            'password' => 'La contraseña es incorrecta. Tu cuenta no se eliminó.',
        ];
        return $mensajes[$codigo] ?? null;
    }

    // GET /perfil/editar
    public function editar()
    {
        $this->exigirSesion();

        $usuario = $this->usuarioModel->buscarPorId($_SESSION['usuario_id']);

        if (!$usuario) {
            header('Location:' . $this->appConfig['base_path_url'] . $this->appConfig['logout_url']);
            exit();
        }

        $this->mostrarFormularioEdicion($usuario, $usuario);
    }

// POST /perfil/actualizar
    public function actualizar()
    {
        $this->exigirSesion();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location:' . $this->appConfig['base_path_url'] . '/perfil/editar');
            exit();
        }

        $id = $_SESSION['usuario_id'];
        $actual = $this->usuarioModel->buscarPorId($id);

        $nombre  = trim($_POST['nombre'] ?? '');
        $usuario = trim($_POST['usuario'] ?? '');
        $fecha   = trim($_POST['fecha_nacimiento'] ?? '');
        $sexo    = $_POST['sexo'] ?? '';
        $pais    = trim($_POST['pais'] ?? '');
        $ciudad  = trim($_POST['ciudad'] ?? '');

        // Si no tocó el mapa, se conserva la ubicación guardada
        $latitud  = $this->coordenadaValida($_POST['latitud'] ?? '', 90) ?? $actual['latitud'];
        $longitud = $this->coordenadaValida($_POST['longitud'] ?? '', 180) ?? $actual['longitud'];

        // Lo que el usuario escribió, para no perderlo si hay un error
        $enviado = [
            'nombre' => $nombre, 'usuario' => $usuario, 'email' => $actual['email'],
            'fecha_nacimiento' => $fecha, 'sexo' => $sexo,
            'pais' => $pais, 'ciudad' => $ciudad,
            'latitud' => $latitud, 'longitud' => $longitud,
        ];

        $error = $this->validarEdicion($id, $nombre, $usuario, $fecha, $sexo, $pais, $ciudad);

        if ($error !== null) {
            $this->mostrarFormularioEdicion($enviado, $actual, $error);
            return;
        }

        $this->usuarioModel->actualizarPerfil($id, $nombre, $usuario, $fecha, $sexo, $pais, $ciudad, $latitud, $longitud);

        // El header/lobby pueden usar el nombre de usuario de la sesión
        $_SESSION['usuario'] = $usuario;

        $this->volverAlPerfil('?perfil=ok');
    }

    private function validarEdicion($id, $nombre, $usuario, $fecha, $sexo, $pais, $ciudad)
    {
        if ($nombre === '' || mb_strlen($nombre) > 100) {
            return 'Ingresá tu nombre completo (máximo 100 caracteres).';
        }

        if (!preg_match('/^\S{3,50}$/u', $usuario)) {
            return 'El usuario debe tener entre 3 y 50 caracteres y no puede tener espacios.';
        }

        if ($this->usuarioModel->existeUsuarioDeOtro($usuario, $id)) {
            return 'Ese nombre de usuario ya está en uso.';
        }

        $d = \DateTime::createFromFormat('Y-m-d', $fecha);
        if (!$d || $d->format('Y-m-d') !== $fecha || $d > new \DateTime('today')) {
            return 'La fecha de nacimiento no es válida.';
        }

        if (!in_array($sexo, ['MASCULINO', 'FEMENINO', 'OTRO', 'PREFIERO_NO_DECIR'], true)) {
            return 'Elegí una opción de sexo válida.';
        }

        if ($pais === '' || $ciudad === '') {
            return 'Seleccioná tu ubicación en el mapa.';
        }

        return null;
    }

// Devuelve el número si es una coordenada válida, o null
    private function coordenadaValida($valor, $maximo)
    {
        if ($valor === '' || !is_numeric($valor) || abs((float) $valor) > $maximo) {
            return null;
        }
        return $valor;
    }

    private function mostrarFormularioEdicion($datos, $actual, $error = null)
    {
        $latitud  = !empty($datos['latitud']) ? $datos['latitud'] : $this->mapConfig['default_lat'];
        $longitud = !empty($datos['longitud']) ? $datos['longitud'] : $this->mapConfig['default_lng'];
        $sexo     = $datos['sexo'] ?? '';

        $data = array_merge($this->datosHeader(), [
            'nombre'           => $datos['nombre'] ?? '',
            'usuario'          => $datos['usuario'] ?? '',
            'email'            => $actual['email'] ?? '',
            'fecha_nacimiento' => $datos['fecha_nacimiento'] ?? '',
            'fecha_max'        => date('Y-m-d'),
            'sexo_masculino'   => $sexo === 'MASCULINO',
            'sexo_femenino'    => $sexo === 'FEMENINO',
            'sexo_otro'        => $sexo === 'OTRO',
            'sexo_no_decir'    => $sexo === 'PREFIERO_NO_DECIR',
            'pais'             => $datos['pais'] ?? '',
            'ciudad'           => $datos['ciudad'] ?? '',
            'error'            => $error,

            'actualizar_url'   => $this->appConfig['base_path_url'] . '/perfil/actualizar',
            'cancelar_url'     => $this->appConfig['base_path_url'] . $this->appConfig['perfil_url'],
            'base_path_url'    => $this->appConfig['base_path_url'],

            // Mapa centrado en la ubicación actual del usuario
            'tile_layer'      => $this->mapConfig['tile_layer'],
            'reverse_geocode' => $this->mapConfig['reverse_geocode'],
            'default_lat'     => $latitud,
            'default_lng'     => $longitud,
            'default_zoom'    => $this->mapConfig['default_zoom'],
            'modo_edicion'    => true,
        ]);

        $this->render->renderiza('perfilEditar', $data);
    }

    private function mensajePerfil($codigo)
    {
        return $codigo === 'ok' ? 'Tus datos se actualizaron correctamente.' : null;
    }


    public function ver()
    {
        $nombreUsuario = trim($_GET['usuario'] ?? '');

        // Si el perfil pedido es el propio, se muestra la versión editable
        if ($nombreUsuario !== '' && strcasecmp($nombreUsuario, $_SESSION['usuario'] ?? '') === 0) {
            header('Location:' . $this->appConfig['base_path_url'] . $this->appConfig['perfil_url']);
            exit();
        }
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

    private function exigirSesion()
    {
        if (!isset($_SESSION['usuario_id'])) {
            header('Location:' . $this->appConfig['base_path_url'] . $this->appConfig['login_show_url']);
            exit();
        }
    }

    private function volverAlPerfil($query = '')
    {
        header('Location:' . $this->appConfig['base_path_url'] . $this->appConfig['perfil_url'] . $query);
        exit();
    }

    private function mensajeExito($codigo)
    {
        $mensajes = [
            'ok'        => 'Tu foto de perfil se actualizó.',
            'eliminada' => 'Tu foto de perfil se eliminó.',
        ];
        return $mensajes[$codigo] ?? null;
    }

    private function mensajeError($codigo)
    {
        $mensajes = [
            'vacio'  => 'Elegí una imagen antes de subir.',
            'tamano' => 'La imagen es demasiado grande (máximo 2 MB).',
            'tipo'   => 'El archivo tiene que ser una imagen JPG, PNG, WEBP o GIF.',
            'subida' => 'No se pudo subir la imagen. Probá de nuevo.',
        ];
        return $mensajes[$codigo] ?? null;
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