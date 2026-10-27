<?php

namespace app\model;

class PartidaModel
{
    private $database;

    public function __construct($database)
    {
        $this->database = $database;
    }

    public function getPartidasDeUsuario(int $usuario_id)
    {
        $sql = "SELECT id, puntaje, fecha
                FROM partidas
                WHERE usuario_id = ?
                ORDER BY fecha DESC";
        return $this->database->queryAll($sql, [$usuario_id]);
    }

    public function getPartidaEnCurso(int $usuarioId): ?array
    {
        return $this->database->queryOne(
            "SELECT id, puntaje, pregunta_actual_id
             FROM partidas
             WHERE usuario_id = ? AND estado = 'EN_CURSO'
             ORDER BY id DESC
             LIMIT 1",
            [$usuarioId]
        );
    }
    public function getPartida(int $partidaId, int $usuarioId): ?array
    {
        return $this->database->queryOne(
            "SELECT id, puntaje, estado, pregunta_actual_id, fecha
             FROM partidas
             WHERE id = ? AND usuario_id = ?",
            [$partidaId, $usuarioId]
        );
    }

    public function crearPartida(int $usuarioId): int
    {
        $this->database->execute(
            "INSERT INTO partidas (usuario_id, puntaje, estado) VALUES (?, 0, 'EN_CURSO')",
            [$usuarioId]
        );
        return (int) $this->database->lastInsertId();
    }

   
    public function asignarSiguientePregunta(int $partidaId): bool
    {
        $fila = $this->database->queryOne(
            "SELECT p.id
             FROM preguntas p
             WHERE p.estado = 'ACTIVA'
               AND NOT EXISTS (
                   SELECT 1 FROM partida_pregunta pp
                   WHERE pp.partida_id = ? AND pp.pregunta_id = p.id
               )
             ORDER BY RAND()
             LIMIT 1",
            [$partidaId]
        );

        if ($fila === null) {
            return false;
        }

        $preguntaId = (int) $fila['id'];

        $this->database->execute(
            "INSERT INTO partida_pregunta (partida_id, pregunta_id) VALUES (?, ?)",
            [$partidaId, $preguntaId]
        );
        $this->database->execute(
            "UPDATE partidas SET pregunta_actual_id = ? WHERE id = ?",
            [$preguntaId, $partidaId]
        );

        return true;
    }
    public function getPregunta(int $preguntaId): ?array
    {
        $pregunta = $this->database->queryOne(
            "SELECT p.id, p.enunciado, c.nombre AS categoria, c.color
             FROM preguntas p
             JOIN categorias c ON c.id = p.categoria_id
             WHERE p.id = ?",
            [$preguntaId]
        );

        if ($pregunta === null) {
            return null;
        }

        $respuestas = $this->database->queryAll(
            "SELECT id, texto FROM respuestas WHERE pregunta_id = ?",
            [$preguntaId]
        );

        return [
            'id' => (int) $pregunta['id'],
            'enunciado' => $pregunta['enunciado'],
            'categoria' => $pregunta['categoria'],
            'color' => $pregunta['color'],
            'respuestas' => $respuestas,
        ];
    }
    public function registrarRespuesta(int $partidaId, int $preguntaId, int $respuestaId): ?bool
    {
        $respuesta = $this->database->queryOne(
            "SELECT es_correcta FROM respuestas WHERE id = ? AND pregunta_id = ?",
            [$respuestaId, $preguntaId]
        );

        if ($respuesta === null) {
            return null;
        }

        $acerto = (int) $respuesta['es_correcta'];

        $filas = $this->database->execute(
            "UPDATE partida_pregunta
             SET respuesta_elegida_id = ?, acerto = ?
             WHERE partida_id = ? AND pregunta_id = ? AND respuesta_elegida_id IS NULL",
            [$respuestaId, $acerto, $partidaId, $preguntaId]
        );

        if ($filas !== 1) {
            return null;
        }

        return $acerto === 1;
    }

    public function sumarPunto(int $partidaId): void
    {
        $this->database->execute(
            "UPDATE partidas SET puntaje = puntaje + 1 WHERE id = ?",
            [$partidaId]
        );
    }

    public function terminarPartida(int $partidaId): void
    {
        $this->database->execute(
            "UPDATE partidas SET estado = 'TERMINADA', pregunta_actual_id = NULL WHERE id = ?",
            [$partidaId]
        );
    }
    public function getDetalleError(int $partidaId): ?array
    {
        return $this->database->queryOne(
            "SELECT p.enunciado,
                    c.nombre AS categoria,
                    c.color,
                    (SELECT r.texto FROM respuestas r
                      WHERE r.pregunta_id = p.id AND r.es_correcta = 1
                      LIMIT 1) AS correcta,
                    (SELECT r2.texto FROM respuestas r2
                      WHERE r2.id = pp.respuesta_elegida_id) AS elegida
             FROM partida_pregunta pp
             JOIN preguntas p ON p.id = pp.pregunta_id
             JOIN categorias c ON c.id = p.categoria_id
             WHERE pp.partida_id = ? AND pp.acerto = 0
             ORDER BY pp.id DESC
             LIMIT 1",
            [$partidaId]
        );
    }
}