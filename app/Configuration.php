<?php

namespace app;

use app\controller\EventoController;
use app\controller\HomeController;
use app\controller\LoginController;
use app\controller\LugaresController;
use app\controller\RegistroController;
use app\controller\ReservaController;
use app\model\EquipoModel;
use app\model\EventoModel;
use app\model\LoginModel;
use app\model\LugaresModel;
use app\model\ReservaModel;
use app\model\UsuarioModel;
use MustacheRender;
use MyDatabase;
use Router;

require_once("helper/Autoloader.php");



class Configuration
{
    private $config;

    public function __construct()
    {
        $this->config = parse_ini_file("config/config.ini", true);
    }

    public function getLoginController()
    {
        return new LoginController(
          $this->getLoginModel(),
          $this->getRender()
      );
    }
    public function getRegistroController(){
        return new RegistroController(
            $this->getRender(),
            $this->getUsuarioModel(),
            $this->getMapConfig()
      );
    }

    public function getHomeController(){
        return new HomeController(
            $this->getRender(),
            $this->getEquipoModel()
        );
    }

    // Los privados
    private function getLoginModel()
    {
        return new LoginModel(
            $this->getDatabase()
        );
    }

    public function getUsuarioModel()
    {
        return new UsuarioModel(
            $this->getDatabase()
        );
    }

    private function getEquipoModel(){
        return new EquipoModel();
    }

    private function getDatabase()
    {
        return new MyDatabase(
            $this->config["BD"]["db_host"],
            $this->config["BD"]["db_user"],
            $this->config["BD"]["db_pass"],
            $this->config["BD"]["db_name"]
        );
    }

    public function getMapConfig()
    {
        return [
            "tile_layer" => $this->config["map"]["tile_layer"],
            "reverse_geocode" => $this->config["map"]["reverse_geocode"],
            "default_lat" => $this->config["map"]["default_lat"],
            "default_lng" => $this->config["map"]["default_lng"],
            "default_zoom" => $this->config["map"]["default_zoom"]
        ];
    }

    private function getRender()
    {
        return new MustacheRender();
    }

    public function getRouter()
    {
        return new Router($this, "home", "show");
    }
}
