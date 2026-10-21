<?php

namespace app;

use app\controller\EventoController;
use app\controller\HomeController;
use app\controller\LoginController;
use app\controller\LugaresController;
use app\controller\RankingController;
use app\controller\RegistroController;
use app\controller\ReservaController;
use app\model\EquipoModel;
use app\model\EventoModel;
use app\model\LoginModel;
use app\model\LugaresModel;
use app\model\RankingModel;
use app\model\ReservaModel;
use app\model\UsuarioModel;
use MustacheRender;
use MyDatabase;
use Redirect;
use Router;

require_once("helper/Autoloader.php");



class Configuration
{

    public function __construct()
    {
        $this->inicializarRedirect();
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
            $this->getUsuarioModel()
      );
    }

    public function getHomeController(){
        return new HomeController(
            $this->getRender(),
            $this->getEquipoModel()
        );
    }

    public function getRankingController(){
        return new RankingController(
            $this->getRender(),
            $this->getRankingModel()
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

    private function getRankingModel(){
        return new RankingModel(
            $this->getDatabase()
        );
    }
    private function getDatabase()
    {
        $config = parse_ini_file("config/config.ini");

        return new MyDatabase($config["db_host"],
            $config["db_user"],
            $config["db_pass"],
            $config["db_name"]
        );
    }

    private function inicializarRedirect(){
        $config = parse_ini_file("config/config.ini");

        Redirect::setBaseDir($config["base_dir"] ?? '/');
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
