<?php

namespace app\service;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailService
{
    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function enviarValidacion(
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

            $mail->Subject = 'Validación de cuenta';

            $mail->Body = "
                <h2>Bienvenido a Preguntones</h2>

                <p>
                    Para activar tu cuenta hacé click en el siguiente enlace:
                </p>

                <p>
                    <a href='{$linkValidacion}'>
                        Validar cuenta
                    </a>
                </p>
            ";

            return $mail->send();

        } catch (Exception $e) {

            error_log($e->getMessage());

            return false;

        }
    }
}
