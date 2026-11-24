<?php

class Auth
{
    public static function exigirSesion($appConfig)
    {
        if (!isset($_SESSION['usuario_id'])) {

            header(
                'Location:' .
                $appConfig['base_path_url'] .
                $appConfig['login_show_url']
            );

            exit();
        }
    }

    public static function exigirAdmin($appConfig)
    {
        self::exigirSesion($appConfig);

        if (($_SESSION['rol'] ?? '') !== 'ADMIN') {

            header(
                'Location:' .
                $appConfig['base_path_url'] .
                $appConfig['lobby_url']
            );

            exit();
        }
    }

    public static function exigirJugador($appConfig)
    {
        self::exigirSesion($appConfig);

        if (($_SESSION['rol'] ?? '') !== 'JUGADOR') {

            header(
                'Location:' .
                $appConfig['base_path_url'] .
                '/admin/show'
            );

            exit();
        }
    }
}