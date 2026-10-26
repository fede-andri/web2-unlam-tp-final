<?php

namespace app;

use app\controller\EventoController;
use app\controller\HomeController;
use app\controller\LobbyController;
use app\controller\LoginController;
use app\controller\LugaresController;
use app\controller\PartidaController;
use app\controller\RankingController;
use app\controller\RegistroController;
use app\controller\ReservaController;
use app\model\EquipoModel;
use app\model\EventoModel;
use app\model\LobbyModel;
use app\model\LoginModel;
use app\model\LugaresModel;
use app\model\PartidaModel;
use app\model\RankingModel;
use app\model\ReservaModel;
use app\model\UsuarioModel;
use app\service\MailService;
use MustacheRender;
use MyDatabase;
use Redirect;
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
          $this->getRender(),
          $this->getAppConfig()
      );
    }
    public function getRegistroController(){
        return new RegistroController(
            $this->getRender(),
            $this->getUsuarioModel(),
            $this->getMapConfig(),
            $this->getAppConfig(),
            $this->getMailService()
      );
    }

    public function getRankingController(){
        return new RankingController(
            $this->getRender(),
            $this->getRankingModel(),
            $this->getAppConfig()
        );
    }
    public function getLobbyController(){
        return new LobbyController(
            $this->getRender(),
            $this->getLobbyModel(),
            $this->getAppConfig()
        );
    }

    public function getPartidaController(){
        return new PartidaController($this->getRender(), $this->getPartidaModel());
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

    private function getLobbyModel(){
        return new LobbyModel();
    }

    public function getMailService()
    {
        return new MailService(
            $this->getMailConfig()
        );
    }

    public function getAppConfig()
    {
        return [
            "base_url" => $this->config["app"]["base_url"],
            "base_path_url" => $this->config["app"]["base_path_url"],
            "login_show_url" => $this->config["app"]["login_show_url"],
            "login_url" => $this->config["app"]["login_url"],
            "logout_url" => $this->config["app"]["logout_url"],
            "registro_url" => $this->config["app"]["registro_url"],
            "registrar_url" => $this->config["app"]["registrar_url"],
            "validacion_url" => $this->config["app"]["validacion_url"],
            "home_url" => $this->config["app"]["home_url"],
            "ranking_show_url"=>$this->config["app"]["ranking_show_url"],
            "lobby_url" => $this->config["app"]["lobby_url"],
        ];
    }

    private function getRankingModel(){
        return new RankingModel(
            $this->getDatabase()
        );
    }

    private function getPartidaModel(){
        return new PartidaModel($this->getDatabase());
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

    public function getMailConfig()
    {
        return [
            "host" => $this->config["mail"]["host"],
            "port" => $this->config["mail"]["port"],
            "username" => $this->config["mail"]["username"],
            "password" => $this->config["mail"]["password"],
            "from_email" => $this->config["mail"]["from_email"],
            "from_name" => $this->config["mail"]["from_name"]
        ];
    }


    private function getRender()
    {
        return new MustacheRender();
    }

    public function getRouter()
    {
        return new Router($this, "lobby", "show");
    }
}
