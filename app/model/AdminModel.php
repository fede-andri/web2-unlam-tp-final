<?php

namespace app\model;

class AdminModel
{

    private $database;

    public function __construct($database){
        $this->database = $database;
    }
    public function contarJugadores(){
        $resultadoTotalJugadores = $this->database->queryOne("
            SELECT COUNT(*) AS total
            FROM usuarios
            WHERE rol = 'JUGADOR'
        ");
        return (int)$resultadoTotalJugadores['total'];
    }

    public function contarPartidas(){
        $resultadoTotalPartidas = $this->database->queryOne("
            SELECT COUNT(*) AS total
            FROM partidas
        ");
        return (int)$resultadoTotalPartidas['total'];
    }

    public function contarPreguntasCreadas()
    {
        $resultadoTotalPreguntas = $this->database->queryOne("
            SELECT COUNT(*) AS total
            FROM preguntas"
        );
        return (int)$resultadoTotalPreguntas['total'];
    }

    public function contarUsuariosNuevosHoy(){
        $resultado = $this->database->queryOne("
            SELECT COUNT(*) AS total
            FROM usuarios
            WHERE DATE(fecha_creacion) = CURDATE()
        ");
        return (int)$resultado['total'];
    }

    public function obtenerUsuariosPorPais()
    {
        $sql = "
            SELECT
                pais,
                COUNT(*) AS total
            FROM usuarios
            WHERE rol = 'JUGADOR'
            GROUP BY pais
            ORDER BY total DESC
        ";
        return $this->database->queryAll($sql);
    }

    public function obtenerUsuariosPorSexo()
    {
        $sql = "
        SELECT
            sexo,
            COUNT(*) AS total
        FROM usuarios
        WHERE rol = 'JUGADOR'
        GROUP BY sexo
    ";

        $resultado = $this->database->queryAll($sql);
        $sexos = [
            'MASCULINO' => 0,
            'FEMENINO' => 0,
            'OTRO' => 0,
            'PREFIERO_NO_DECIR' => 0
        ];

        foreach ($resultado as $fila) {
            $sexos[$fila['sexo']] =
                (int) $fila['total'];
        }

        return [
            [
                'sexo' => 'Masculino',
                'total' => $sexos['MASCULINO']
            ],
            [
                'sexo' => 'Femenino',
                'total' => $sexos['FEMENINO']
            ],
            [
                'sexo' => 'Otro',
                'total' => $sexos['OTRO']
            ],
            [
                'sexo' => 'Prefiero no decir',
                'total' => $sexos['PREFIERO_NO_DECIR']
            ]
        ];
    }

    public function obtenerUsuariosPorEdad()
    {
        $sql = "
        SELECT
            TIMESTAMPDIFF(
                YEAR,
                fecha_nacimiento,
                CURDATE()
            ) AS edad
        FROM usuarios
        WHERE rol = 'JUGADOR'
    ";

        $resultado = $this->database->queryAll($sql);
        $rangos = [
            'Menores de 18' => 0,
            '18 - 25' => 0,
            '26 - 35' => 0,
            '36 - 50' => 0,
            'Más de 50' => 0
        ];

        foreach ($resultado as $fila) {
            $edad = (int)$fila['edad'];

            if ($edad < 18) {
                $rangos['Menores de 18']++;
            } elseif ($edad <= 25) {
                $rangos['18 - 25']++;
            } elseif ($edad <= 35) {
                $rangos['26 - 35']++;
            } elseif ($edad <= 50) {
                $rangos['36 - 50']++;
            } else {
                $rangos['Más de 50']++;
            }
        }

        return [
            [
                'rango' => 'Menores de 18',
                'total' => $rangos['Menores de 18']
            ],
            [
                'rango' => '18 - 25',
                'total' => $rangos['18 - 25']
            ],
            [
                'rango' => '26 - 35',
                'total' => $rangos['26 - 35']
            ],
            [
                'rango' => '36 - 50',
                'total' => $rangos['36 - 50']
            ],
            [
                'rango' => 'Más de 50',
                'total' => $rangos['Más de 50']
            ]
        ];
    }

    public function obtenerAciertosPorRango()
    {
        $sql = "
            SELECT
                p.usuario_id,
                SUM(
                    CASE
                        WHEN pp.acerto = 1 THEN 1
                        ELSE 0
                    END
                ) AS aciertos,
                COUNT(*) AS respuestas
            FROM partida_pregunta pp
            INNER JOIN partidas p
                ON p.id = pp.partida_id
            GROUP BY p.usuario_id
        ";

        $resultado = $this->database->queryAll($sql);

    $rangos = [
        '0 - 25%'   => 0,
        '26 - 50%'  => 0,
        '51 - 75%'  => 0,
        '76 - 100%' => 0
    ];

    foreach ($resultado as $fila) {
        $respuestas = (int)$fila['respuestas'];
        if ($respuestas === 0) {
            continue;
        }
        $aciertos = (int)$fila['aciertos'];
        $porcentaje = ($aciertos * 100) / $respuestas;

        if ($porcentaje <= 25) {
            $rangos['0 - 25%']++;
        } elseif ($porcentaje <= 50) {
            $rangos['26 - 50%']++;
        } elseif ($porcentaje <= 75) {
            $rangos['51 - 75%']++;
        } else {
            $rangos['76 - 100%']++;
        }
    }
    return [
        [
            'rango' => '0 - 25%',
            'total' => $rangos['0 - 25%']
        ],
        [
            'rango' => '26 - 50%',
            'total' => $rangos['26 - 50%']
        ],
        [
            'rango' => '51 - 75%',
            'total' => $rangos['51 - 75%']
        ],
        [
            'rango' => '76 - 100%',
            'total' => $rangos['76 - 100%']
        ]
    ];
}

}