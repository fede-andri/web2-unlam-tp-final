<?php

namespace app\model;

class EquipoModel
{
    public function obtenerDatosEquipo()
    {
        return [
            'nombre' => 'Nombre del equipo',
            'universidad' => 'UNLaM',
            'materia' => 'Programación Web II',
            'anio' => '2026',
            'integrantes' => [
                ['nombre'=>'Duchi Ricardo'],
                ['nombre'=>'Santillan Sol Ariana'],
                ['nombre'=>'Vazquez Espindola Mario'],
                ['nombre'=>'Maida Eric'],
                ['nombre'=>'Becerra Marcelo'],
                ['nombre'=>'Andrijasevich Federico']
            ]
        ];
    }
}