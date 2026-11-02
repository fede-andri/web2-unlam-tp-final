<?php

namespace app\model;

use MyDatabase;

class RankingModel{

    private $database;

    public function __construct($database){
        $this->database = $database;
    }

    public function getRanking(int $limit = 20){
        $sql = "SELECT u.id, u.usuario,
        COALESCE(SUM(p.puntaje), 0) AS puntaje_total,
        COUNT(p.id) AS partidas_jugadas
        FROM usuarios u
        LEFT JOIN partidas p ON p.usuario_id = u.id
        WHERE u.validado = 1
        GROUP BY u.id, u.usuario
        ORDER BY puntaje_total DESC, u.usuario ASC
        LIMIT $limit";

        return $this->database->queryAll($sql);
    }

    public function getPosicionDeUsuario(int $usuarioId)
    {
        $sql = "SELECT id, usuario, puntaje_total, partidas_jugadas, posicion
            FROM (
                SELECT u.id, u.usuario,
                       COALESCE(SUM(p.puntaje), 0) AS puntaje_total,
                       COUNT(p.id) AS partidas_jugadas,
                       ROW_NUMBER() OVER (
                           ORDER BY COALESCE(SUM(p.puntaje), 0) DESC, u.usuario ASC
                       ) AS posicion
                FROM usuarios u
                LEFT JOIN partidas p ON p.usuario_id = u.id
                WHERE u.validado = 1
                GROUP BY u.id, u.usuario
            ) AS ranking
            WHERE id = ?";

        return $this->database->queryOne($sql, [$usuarioId]);
    }
}