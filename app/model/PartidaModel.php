<?php

namespace app\model;


class PartidaModel{
    private $database;

    public function __construct($database){
        $this->database = $database;
    }

    public function getPartidasDeUsuario(int $usuario_id){

        $sql = "SELECT id, puntaje, fecha 
                FROM partidas 
                WHERE usuario_id = ?
                ORDER BY fecha DESC";
        return $this->database->queryAll($sql, [$usuario_id]);
    }
}