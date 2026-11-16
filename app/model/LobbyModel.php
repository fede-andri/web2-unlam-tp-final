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
            'partidas_jugadas'      => (int) ($partidas['partidas_jugadas'] ?? 0),
            'puntaje_total'         => (int) ($partidas['puntaje_total'] ?? 0),

            'mejor_racha'           => (int) ($partidas['mejor_racha'] ?? 0),
            'preguntas_respondidas' => $respondidas,
            'precision'             => $respondidas > 0 ? (int) round($aciertos / $respondidas * 100) : 0,
        ];
    }
    public function obtenerDatosLobby($usuario)
    {
        return [
            'nivel_requerido_crear_pregunta' => 10,
            'categorias' => [
                ['nombre' => 'Campeones', 'precision' => 74],
                ['nombre' => 'eSports', 'precision' => 70],
                ['nombre' => 'Ítems', 'precision' => 61],
                ['nombre' => 'Runas y hechizos', 'precision' => 52],
                ['nombre' => 'Lore de Runaterra', 'precision' => 47],
            ],
            'ultimas_partidas' => [
                ['fecha' => 'Hoy, 21:40', 'categoria' => 'Campeones', 'aciertos' => 8, 'total' => 10, 'puntaje' => 860, 'victoria' => true],
                ['fecha' => 'Hoy, 19:15', 'categoria' => 'Ítems', 'aciertos' => 5, 'total' => 10, 'puntaje' => 420, 'victoria' => false],
                ['fecha' => 'Ayer, 23:02', 'categoria' => 'Lore de Runaterra', 'aciertos' => 7, 'total' => 10, 'puntaje' => 730, 'victoria' => true],
                ['fecha' => '23/10, 18:30', 'categoria' => 'eSports', 'aciertos' => 9, 'total' => 10, 'puntaje' => 940, 'victoria' => true],
                ['fecha' => '22/10, 22:11', 'categoria' => 'Runas y hechizos', 'aciertos' => 4, 'total' => 10, 'puntaje' => 310, 'victoria' => false],
            ],

        ];
    }
}
