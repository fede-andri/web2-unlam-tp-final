<?php

namespace app\model;

// Datos hardcodeados hasta que existan las tablas de partidas/preguntas/niveles.
// Ranking, la experiencia, el nivel y el rango salen de las partidas reales.
use app\service\ExperienciaService;

class LobbyModel
{
    private $database;
    public function __construct($database)
    {
        $this->database = $database;
    }

    public function getDatosExperiencia(int $usuarioId)
    {
        $fila = $this->database->queryOne(
            "SELECT COALESCE(SUM(puntaje), 0) AS puntos
             FROM partidas
             WHERE usuario_id = ? AND estado = 'TERMINADA'",
            [$usuarioId]
        );

        return ExperienciaService::calcularExperiencia((int) ($fila['puntos'] ?? 0));
    }

    public function getEstadisticas(int $usuarioId)
    {
        $partidas = $this->database->queryOne(
            "SELECT COUNT(*) AS partidas_jugadas,
                    COALESCE(SUM(puntaje), 0) AS puntaje_total,
                    COALESCE(MAX(puntaje), 0) AS mejor_racha
             FROM partidas
             WHERE usuario_id = ? AND estado = 'TERMINADA'",
            [$usuarioId]
        );

        $respuestas = $this->database->queryOne(
            "SELECT COUNT(*) AS respondidas,
                    COALESCE(SUM(pp.acerto), 0) AS aciertos
             FROM partida_pregunta pp
             JOIN partidas p ON p.id = pp.partida_id
             WHERE p.usuario_id = ? AND pp.respuesta_elegida_id IS NOT NULL",
            [$usuarioId]
        );

        $respondidas = (int) ($respuestas['respondidas'] ?? 0);
        $aciertos = (int) ($respuestas['aciertos'] ?? 0);

        return [
            'partidas_jugadas' => (int) ($partidas['partidas_jugadas'] ?? 0),
            'puntaje_total' => (int) ($partidas['puntaje_total'] ?? 0),

            'mejor_racha' => (int) ($partidas['mejor_racha'] ?? 0),
            'preguntas_respondidas' => $respondidas,
            'precision' => $respondidas > 0 ? (int) round($aciertos / $respondidas * 100) : 0,
        ];
    }
    public function getPrecisionPorCategoria(int $usuarioId)
    {
        $filas = $this->database->queryAll(
            "SELECT c.nombre,
                COUNT(r.pregunta_id) AS respondidas,
                COALESCE(SUM(r.acerto), 0) AS aciertos
         FROM categorias c
         LEFT JOIN preguntas q ON q.categoria_id = c.id
         LEFT JOIN (
             SELECT pp.pregunta_id, pp.acerto
             FROM partida_pregunta pp
             JOIN partidas p ON p.id = pp.partida_id
             WHERE p.usuario_id = ? AND pp.respuesta_elegida_id IS NOT NULL
         ) r ON r.pregunta_id = q.id
         GROUP BY c.id, c.nombre
         ORDER BY c.nombre",
            [$usuarioId]
        );

        $categorias = [];
        foreach ($filas as $f) {
            $respondidas = (int) $f['respondidas'];
            $categorias[] = [
                'nombre'    => $f['nombre'],
                'precision' => $respondidas > 0
                    ? (int) round($f['aciertos'] / $respondidas * 100)
                    : 0,
            ];
        }
        return $categorias;
    }
    public function obtenerDatosLobby($usuarioId)
    {
        return [
            'nivel_requerido_crear_pregunta' => 10,
        ];
    }

    public function getUltimasPartidas(int $usuarioId, int $limite = 5): array
    {
        $limite = max(1, min($limite, 20));

        $sql = "SELECT p.id,
                       p.puntaje,
                       p.motivo_fin,
                       COALESCE(c.nombre, 'Sin categoría') AS categoria,
                       SUM(CASE WHEN pp.acerto = 1 THEN 1 ELSE 0 END) AS aciertos,
                       COUNT(pp.id) AS total,
                       SUM(CASE WHEN pp.acerto = 0 THEN 1 ELSE 0 END) AS errores,
                       CASE
                           WHEN DATE(p.fecha) = CURDATE()
                               THEN CONCAT('Hoy, ', DATE_FORMAT(p.fecha, '%H:%i'))
                           WHEN DATE(p.fecha) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)
                               THEN CONCAT('Ayer, ', DATE_FORMAT(p.fecha, '%H:%i'))
                           ELSE DATE_FORMAT(p.fecha, '%d/%m, %H:%i')
                       END AS fecha
                FROM partidas p
                LEFT JOIN categorias c ON c.id = p.categoria_id
                LEFT JOIN partida_pregunta pp ON pp.partida_id = p.id
                WHERE p.usuario_id = ?
                  AND p.estado = 'TERMINADA'
                GROUP BY p.id, p.puntaje, p.motivo_fin, c.nombre, p.fecha
                ORDER BY p.fecha DESC, p.id DESC
                LIMIT $limite";

        return $this->database->queryAll($sql, [$usuarioId]);
    }
}
