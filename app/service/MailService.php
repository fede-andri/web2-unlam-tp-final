<?php

namespace app\service;
require_once __DIR__ . '/../../vendor/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../../vendor/PHPMailer/src/Exception.php';
require_once __DIR__ . '/../../vendor/PHPMailer/src/SMTP.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailService
{
    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function enviarValidacionMail(
        string $email,
        string $nombre,
        string $linkValidacion
    ): bool {

        $mail = new PHPMailer(true);

        try {

            $mail->isSMTP();

            $mail->Host = $this->config['host'];
            $mail->SMTPAuth = true;

            $mail->Username = $this->config['username'];
            $mail->Password = $this->config['password'];

            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

            $mail->Port = $this->config['port'];

            $mail->CharSet = 'UTF-8';

            $mail->setFrom(
                $this->config['from_email'],
                $this->config['from_name']
            );

            $mail->addAddress(
                $email,
                $nombre
            );

            $mail->isHTML(true);

            $mail->Subject = 'Validación de cuenta - Preguntones';

            $mail->Body = $this->cargarTemplate(
                __DIR__ . '/../mail/validacionCuenta.html',
                [
                    'nombre' => $nombre,
                    'link_validacion' => $linkValidacion
                ]
            );
            return $mail->send();

        } catch (Exception $e) {

            error_log($e->getMessage());

            return false;

        }
    }

    private function cargarTemplate(
        string $archivo,
        array $variables
    ): string {

        $html = file_get_contents($archivo);

        foreach ($variables as $clave => $valor) {

            $html = str_replace(
                "{{{$clave}}}",
                $valor,
                $html
            );

        }

        return $html;
    }
}
