<?php

namespace app\model;

// Datos hardcodeados hasta que existan las tablas de partidas/preguntas/niveles.
class LobbyModel
{
    public function obtenerDatosLobby($usuario)
    {
        return [
            'nivel' => 7,
            'nivel_requerido_crear_pregunta' => 10,
            'xp_actual' => 1340,
            'xp_siguiente_nivel' => 2000,
            'rango' => 'Oro II',
            'estadisticas' => [
                'partidas_jugadas' => 24,
                'preguntas_respondidas' => 240,
                'precision' => 68,
                'mejor_racha' => 11,
                'puntaje_total' => 4820,
            ],
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
            'ranking' => [
                ['usuario' => 'Faker_Fan', 'puntaje' => 9870],
                ['usuario' => 'YasuoMain', 'puntaje' => 8420],
                ['usuario' => 'BaronHunter', 'puntaje' => 7155],
                ['usuario' => $usuario, 'puntaje' => 4820],
                ['usuario' => 'TeemoShrooms', 'puntaje' => 3990],
            ],
        ];
    }
}
