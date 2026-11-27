<?php

namespace app\model;

use MyDatabase;

class RankingModel{

    private $database;
    const PERIODO_POR_DEFECTO = 'anio';

    // clave que viaja por la URL (?periodo=...) => texto de la pestaña y etiqueta del panel
    const PERIODOS = [
        'hoy'    => ['nombre' => 'Hoy',       'etiqueta' => 'Partidas de hoy'],
        'semana' => ['nombre' => 'Semana',    'etiqueta' => 'Últimos 7 días'],
        'mes'     => ['nombre' => 'Mes',       'etiqueta' => 'Este mes'],
        'anio'   => ['nombre' => 'Año',       'etiqueta' => 'Este año'],
    ];

    public function __construct($database){
        $this->database = $database;
    }

    public static function esPeriodoValido($periodo)
    {
        return is_string($periodo) && isset(self::PERIODOS[$periodo]);
    }
    public function getRanking(int $limit = 20, string $periodo = self::PERIODO_POR_DEFECTO){
        $periodo = $this->normalizar($periodo);
        $filtro  = $this->filtroDePeriodo($periodo);
        $conPartidas  = $this->soloConPartidas($periodo);

        $sql = "SELECT u.id, u.usuario,
        COALESCE(SUM(p.puntaje), 0) AS puntaje_total,
        COUNT(p.id) AS partidas_jugadas
        FROM usuarios u
        LEFT JOIN partidas p ON p.usuario_id = u.id $filtro
        WHERE u.validado = 1
        GROUP BY u.id, u.usuario
        $conPartidas
        ORDER BY puntaje_total DESC, u.usuario ASC
        LIMIT $limit";

        return $this->database->queryAll($sql);
    }

    public function getPosicionDeUsuario(int $usuarioId, string $periodo = self::PERIODO_POR_DEFECTO)
    {
        $periodo = $this->normalizar($periodo);
        $filtro  = $this->filtroDePeriodo($periodo);
        $conPartidas  = $this->soloConPartidas($periodo);

        $sql = "SELECT id, usuario, puntaje_total, partidas_jugadas, posicion
            FROM (
                SELECT u.id, u.usuario,
                       COALESCE(SUM(p.puntaje), 0) AS puntaje_total,
                       COUNT(p.id) AS partidas_jugadas,
                       ROW_NUMBER() OVER (
                           ORDER BY COALESCE(SUM(p.puntaje), 0) DESC, u.usuario ASC
                       ) AS posicion
                FROM usuarios u
                LEFT JOIN partidas p ON p.usuario_id = u.id $filtro
                WHERE u.validado = 1
                GROUP BY u.id, u.usuario
                $conPartidas
            ) AS ranking
            WHERE id = ?";

        return $this->database->queryOne($sql, [$usuarioId]);
    }
    public function getPosicionesAnteriores(string $periodo = self::PERIODO_POR_DEFECTO)
    {
        $periodo = $this->normalizar($periodo);
        if ($periodo === 'hoy') {
            return []; // "hasta ayer" no tiene partidas de hoy: no hay con que comparar
        }

        $filtro = $this->filtroDePeriodo($periodo) . " AND p.fecha < CURDATE()";
        $having = $this->soloConPartidas($periodo);

        $sql = "SELECT u.id,
                       ROW_NUMBER() OVER (
                           ORDER BY COALESCE(SUM(p.puntaje), 0) DESC, u.usuario ASC
                       ) AS posicion
                FROM usuarios u
                LEFT JOIN partidas p ON p.usuario_id = u.id $filtro
                WHERE u.validado = 1
                GROUP BY u.id, u.usuario
                $having";

        $mapa = [];
        foreach ($this->database->queryAll($sql) as $fila) {
            $mapa[(int) $fila['id']] = (int) $fila['posicion'];
        }
        return $mapa;
    }
    private function normalizar(string $periodo)
    {
        return self::esPeriodoValido($periodo) ? $periodo : self::PERIODO_POR_DEFECTO;
    }

    private function filtroDePeriodo(string $periodo)
    {
        switch ($periodo) {
            case 'hoy':    return "AND p.fecha >= CURDATE()";
            case 'semana': return "AND p.fecha >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            case 'mes' : return "AND p.fecha >= DATE_SUB(NOW(), INTERVAL 1 MONTH)";
            case 'anio':   return "AND p.fecha >= MAKEDATE(YEAR(NOW()), 1)";
            default:       return "";
        }
    }

    private function soloConPartidas(string $periodo)
    {
        return $periodo === 'total' ? "" : "HAVING COUNT(p.id) > 0";
    }
}