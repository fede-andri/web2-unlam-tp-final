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


}