<?php

class Redirect
{
    private static $baseDir = '';

    public static function setBaseDir($dir)
    {
        self::$baseDir = $dir;
    }

    public static function to($url)
    {
        // Limpiamos barras extras al inicio de la ruta recibida
        $url = ltrim($url, '/');

        // Si la URL no incluye ya el protocolo HTTP ni la base del proyecto, se la agregamos de forma absoluta
        if (strpos($url, 'http') === false && (empty(self::$baseDir) || strpos($url,trim(self::$baseDir, '/')) === false)) {
            $url = self::$baseDir . $url;
        }

        header("Location: " . $url);
        exit();
    }

    public static function toIndex()
    {
        self::to('/web2-unlam-tp-final');
    }
}
