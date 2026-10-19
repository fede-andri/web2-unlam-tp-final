<?php

class Redirect
{
    private static $baseDir = '/web2-unlam-tp-final/';

    public static function to($url)
    {
        // Limpiamos barras extras al inicio de la ruta recibida
        $url = ltrim($url, '/');

        // Si la URL no incluye ya el protocolo HTTP ni la base del proyecto, se la agregamos de forma absoluta
        if (strpos($url, 'http') === false && strpos($url, 'web2-unlam-tp-final') === false) {
            $url = self::$baseDir . $url;
        }

        header("Location: " . $url);
        exit();
    }

    public static function toIndex()
    {
        self::to('/');
    }
}
