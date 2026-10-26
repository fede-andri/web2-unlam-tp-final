<?php
use app\Configuration;

session_start();
require_once("app/Configuration.php");

$configuration = new \app\Configuration();
$router = $configuration->getRouter();

$router->dispatch(
        Request::get("controller", "lobby"),
        Request::get("method", "show")
);