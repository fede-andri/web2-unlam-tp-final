<?php

namespace app\model;

use MyDatabase;

class RankingModel{

    private $database;

    public function __construct($database){
        $this->database = $database;
    }

    public function getRanking(){
        $sql = "SELECT u.id, u.usuario,
        COALESCE(SUM(p.puntaje), 0) AS puntaje_total,
        COUNT(p.id) AS partidas_jugadas
        FROM usuarios u
        LEFT JOIN partidas p ON p.usuario_id = u.id
        GROUP BY u.id, u.usuario
        ORDER BY puntaje_total DESC, u.usuario ASC
        LIMIT 20";

        return $this->database->queryAll($sql);
    }


}