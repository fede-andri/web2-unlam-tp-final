<?php

class FotoPerfil
{
    const POR_DEFECTO = 'default.png';
    const MAX_BYTES = 2097152; // 2 MB

    private static function directorio()
    {
        return __DIR__ . '/../public/img/perfiles/';
    }


    public static function guardar($archivo)
    {
        if (!$archivo || $archivo['error'] === UPLOAD_ERR_NO_FILE) {
            return ['foto' => null, 'error' => null];
        }

        if ($archivo['error'] === UPLOAD_ERR_INI_SIZE || $archivo['error'] === UPLOAD_ERR_FORM_SIZE) {
            return ['foto' => null, 'error' => 'tamano'];
        }

        if ($archivo['error'] !== UPLOAD_ERR_OK) {
            return ['foto' => null, 'error' => 'subida'];
        }

        if ($archivo['size'] > self::MAX_BYTES) {
            return ['foto' => null, 'error' => 'tamano'];
        }

        // Validar el contenido real, no la extensión
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($archivo['tmp_name']);

        $extensiones = [
            'image/jpeg' => 'jpg',
            'image/png'  => 'png',
            'image/webp' => 'webp',
            'image/gif'  => 'gif',
        ];

        if (!isset($extensiones[$mime])) {
            return ['foto' => null, 'error' => 'tipo'];
        }

        $dir = self::directorio();
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $nombre = bin2hex(random_bytes(16)) . '.' . $extensiones[$mime];

        if (!move_uploaded_file($archivo['tmp_name'], $dir . $nombre)) {
            return ['foto' => null, 'error' => 'subida'];
        }

        return ['foto' => $nombre, 'error' => null];
    }


    public static function eliminar($nombre)
    {
        if (empty($nombre) || $nombre === self::POR_DEFECTO) {
            return;
        }
        $ruta = self::directorio() . basename($nombre);
        if (is_file($ruta)) {
            unlink($ruta);
        }
    }
}