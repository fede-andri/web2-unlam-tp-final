<?php

namespace app\model;

class PartidaModel
{
    private $database;
    const SEGUNDOS_POR_PREGUNTA = 10;
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

    public function iniciarTemporizador(int $partida_id){
        $sql =     "UPDATE partidas
                    SET pregunta_actual_id = ?,
                    pregunta_expira_en = DATE_ADD(NOW(), INTERVAL " . self::SEGUNDOS_POR_PREGUNTA . " SECOND)
                    WHERE id = ?";
        $this->database->execute($sql, [$partida_id]);
    }

    public function segundosRestantes(int $partida_id){
        $sql = "SELECT pregunta_expira_en IS NULL AS sin_temporizador,
                   GREATEST(TIMESTAMPDIFF(SECOND, NOW(), pregunta_expira_en), 0) AS restantes
            FROM partidas WHERE id = ?";
        $fila = $this->database->queryOne($sql, [$partida_id]);

        if ($fila === null || !empty($fila['sin_temporizador'])) {
            return null;
        }
        return (int) $fila['restantes'];
    }

    public function tiempoAgotado(int $partidaId): bool
    {
        $sql = "SELECT (pregunta_expira_en IS NOT NULL
                    AND NOW() > DATE_ADD(pregunta_expira_en, INTERVAL 1 SECOND)) AS vencida
            FROM partidas WHERE id = ?";
        $fila = $this->database->queryOne($sql, [$partidaId]);
        return !empty($fila['vencida']);
    }
    public function terminarPorTiempo(int $partidaId)
    {
        $this->terminarPartida($partidaId);   // reutiliza lo que ya hace al terminar una partida

        $this->database->execute(
            "UPDATE partidas 
             SET motivo_fin = 'TIEMPO', pregunta_expira_en = NULL 
             WHERE id = ?",
            [$partidaId]
        );
    }
    public function getPartidaEnCurso(int $usuarioId)
    {
        return $this->database->queryOne(
            "SELECT id, puntaje, pregunta_actual_id
             FROM partidas
             WHERE usuario_id = ? AND estado = 'EN_CURSO' AND pregunta_actual_id IS NOT NULL
             ORDER BY id DESC
             LIMIT 1",
            [$usuarioId]
        );
    }
    public function getPartida(int $partidaId, int $usuarioId)
    {
        return $this->database->queryOne(
            "SELECT id, puntaje, estado, pregunta_actual_id, fecha, motivo_fin
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
            "UPDATE partidas
            SET pregunta_actual_id = ?,
            pregunta_expira_en = DATE_ADD(NOW(), INTERVAL " . self::SEGUNDOS_POR_PREGUNTA . " SECOND)
            WHERE id = ?",
            [$preguntaId, $partidaId]
        );
        return true;
    }
    public function getPregunta(int $preguntaId)
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

    public function sumarPunto(int $partidaId)
    {
        $this->database->execute(
            "UPDATE partidas SET puntaje = puntaje + 1 WHERE id = ?",
            [$partidaId]
        );
    }

    public function terminarPartida(int $partidaId)
    {
        $this->database->execute(
            "UPDATE partidas SET estado = 'TERMINADA', pregunta_actual_id = NULL WHERE id = ?",
            [$partidaId]
        );
    }
    public function getDetalleError(int $partidaId)
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

    public function reportarPregunta(int $preguntaId, int $usuarioId, string $motivo): void
    {
        $this->database->execute(
            "UPDATE preguntas SET estado = 'REPORTADA' WHERE id = ?",
            [$preguntaId]
        );

        $this->database->execute(
            "INSERT INTO reportes (pregunta_id, usuario_id, motivo) VALUES (?, ?, ?)",
            [$preguntaId, $usuarioId, $motivo]
        );
    }

    public function reemplazarPreguntaReportada(int $partidaId, int $preguntaId): bool
    {
        $this->database->execute(
            "DELETE FROM partida_pregunta WHERE partida_id = ? AND pregunta_id = ? AND respuesta_elegida_id IS NULL",
            [$partidaId, $preguntaId]
        );

        return $this->asignarSiguientePregunta($partidaId);
    }
}