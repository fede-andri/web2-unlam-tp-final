<?php

class MyDatabase
{
    private $conexion;

    public function __construct($host, $user, $pass, $db)
    {
        $this->conexion = new PDO(
            "mysql:host=$host;dbname=$db;charset=utf8mb4",
            $user,
            $pass,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]
        );
    }

    public function queryOne(string $sql, array $params = [])
    {
        Log::info($sql);
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute($params);
        $fila = $stmt->fetch();
        return $fila === false ? null : $fila;
    }

    public function queryAll(string $sql, array $params = [])
    {
        Log::info($sql);
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function execute(string $sql, array $params = [])
    {
        Log::info($sql);
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    public function lastInsertId()
    {
        return $this->conexion->lastInsertId();
    }
}