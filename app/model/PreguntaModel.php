<?php

namespace app\model;

class PreguntaModel
{
    private $database;

    public function __construct($database)
    {
        $this->database = $database;
    }

    public function getCategorias()
    {
        return $this->database->queryAll("SELECT id, nombre FROM categorias");
    }

    public function sugerirPregunta(int $categoriaId, string $enunciado, array $respuestas): void
    {
        $this->database->execute(
            "INSERT INTO preguntas (categoria_id, enunciado, estado) VALUES (?, ?, 'PENDIENTE')",
            [$categoriaId, $enunciado]
        );
        $preguntaId = (int) $this->database->lastInsertId();

        foreach ($respuestas as $resp) {
            $this->database->execute(
                "INSERT INTO respuestas (pregunta_id, texto, es_correcta) VALUES (?, ?, ?)",
                [$preguntaId, $resp['texto'], $resp['es_correcta'] ? 1 : 0]
            );
        }
    }

    public function reportarPregunta(int $preguntaId): void
    {
        $this->database->execute(
            "UPDATE preguntas SET estado = 'REPORTADA' WHERE id = ?",
            [$preguntaId]
        );
    }

    public function getPreguntasPendientes(): array
    {
        $preguntas = $this->database->queryAll(
            "SELECT p.id, p.enunciado, p.fecha_creacion, c.nombre AS categoria, c.color
             FROM preguntas p
             JOIN categorias c ON c.id = p.categoria_id
             WHERE p.estado = 'PENDIENTE'
             ORDER BY p.fecha_creacion DESC, p.id DESC"
        );

        foreach ($preguntas as &$pregunta) {
            $pregunta['respuestas'] = $this->database->queryAll(
                "SELECT id, texto, es_correcta
                 FROM respuestas
                 WHERE pregunta_id = ?
                 ORDER BY id ASC",
                [(int) $pregunta['id']]
            );
        }
        unset($pregunta);

        return $preguntas;
    }

    public function contarPreguntasPendientes(): int
    {
        $fila = $this->database->queryOne(
            "SELECT COUNT(*) AS total FROM preguntas WHERE estado = 'PENDIENTE'"
        );
        return $fila ? (int) $fila['total'] : 0;
    }

    public function cambiarEstadoPregunta(int $preguntaId, string $estado): void
    {
        $estadosValidos = ['ACTIVA', 'BAJA', 'PENDIENTE', 'REPORTADA'];
        if (!in_array($estado, $estadosValidos, true)) {
            return;
        }

        $this->database->execute(
            "UPDATE preguntas SET estado = ? WHERE id = ?",
            [$estado, $preguntaId]
        );
    }
}
