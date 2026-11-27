<?php

namespace app\service;

class ExperienciaService{

    const XP_POR_PUNTO = 200;
    const XP_POR_NIVEL = 2000;

    // Rangos lol
    const RANGOS = ['Hierro', 'Bronce', 'Plata', 'Oro', 'Platino', 'Esmeralda', 'Diamante', 'Maestro', 'Gran Maestro', 'Retador'];
    const NIVELES_POR_RANGO = 4;     // cada rango dura 4 niveles (las 4 divisiones)
    const RANGOS_CON_DIVISION = 7;   // Hierro..Diamante tienen divisiones IV, III, II, I
    const CLASES_RANGO = ['hierro', 'bronce', 'plata', 'oro', 'platino', 'esmeralda', 'diamante', 'maestro', 'gran-maestro', 'retador'];
    public static function calcularExperiencia($puntosTotales){
        $xp = $puntosTotales * self::XP_POR_PUNTO;
        $enNivel = $xp % self::XP_POR_NIVEL;
        $nivel = intdiv($xp, self::XP_POR_NIVEL) + 1;

        return[
            'nivel' => intdiv($xp, self::XP_POR_NIVEL) + 1,
            'rango' => self::rangoDeNivel($nivel),
            'rango_clase' => self::CLASES_RANGO[self::indiceDeRango($nivel)],
            'xp_total' => $xp,
            'xp_actual' => $enNivel,
            'xp_necesaria' => self::XP_POR_NIVEL,
            'xp_porcentaje' => (int) round($enNivel / self::XP_POR_NIVEL * 100)
        ];
    }

    private static function indiceDeRango(int $nivel)
    {
        return min(intdiv(max($nivel, 1) - 1, self::NIVELES_POR_RANGO), count(self::RANGOS) - 1);
    }
    private static function rangoDeNivel($nivel){
        $indice = self::indiceDeRango($nivel);
        $nombre = self::RANGOS[$indice];

        if ($indice >= self::RANGOS_CON_DIVISION) {
            return $nombre; // Maestro, Gran Maestro y Retador no tienen divisiones
        }

        $division = self::NIVELES_POR_RANGO - ((max($nivel, 1) - 1) % self::NIVELES_POR_RANGO); // 4,3,2,1
        $romanos = [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV'];

        return $nombre . ' ' . $romanos[$division];
    }
}