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
}
