<?php

require_once __DIR__ . "/../vendor/mustache/Mustache/Autoloader.php";
Mustache_Autoloader::register(__DIR__ . "/../vendor/mustache");

class MustacheRender
{
    private $mustache;
    private $viewPath;
    private $datosGlobales;

    public function __construct(array $datosGlobales = [])
    {
        $this->viewPath = dirname(__DIR__) . "/app/view";
        $this->datosGlobales = $datosGlobales;

        $this->mustache = new Mustache_Engine([
            "loader" => new Mustache_Loader_FilesystemLoader($this->viewPath, ["extension" => ".mustache"])
        ]);
    }

    public function renderiza($view, $data = [])
    {
        require_once($this->viewPath . "/header.php");
        echo $this->mustache->render(
            $view . "View",
            array_merge($this->datosGlobales, $data)
        );
        require_once($this->viewPath . "/footer.php");
    }
}
