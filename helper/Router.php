<?php

class Router
{
    private $config;
    private $defaultController;
    private $defaultMethod;

    public function __construct($config, $defaultController, $defaultMethod)
    {
        $this->config            = $config;
        $this->defaultController = $defaultController;
        $this->defaultMethod     = $defaultMethod;
    }

    public function dispatch($controllerName, $method)
    {
        $this->autorizar(
            $controllerName,
            $method
        );

        $controller = $this->getController($controllerName);

        $method = $this->getMethod(
            $controller,
            $method
        );

        $controller->{$method}();
    }

    private function getController($controllerName)
    {
        $factoryMethod = $this->getFactoryMethodName($controllerName);

        if (!method_exists($this->config, $factoryMethod)) {
            $factoryMethod = $this->getFactoryMethodName($this->defaultController);
        }

        return $this->config->{$factoryMethod}();
    }

    private function getFactoryMethodName($controllerName)
    {
        return "get" . ucfirst($controllerName) . "Controller";
    }

    private function getMethod($controller, $method)
    {
        return method_exists($controller, $method) ? $method : $this->defaultMethod;
    }

    private function autorizar($controller, $method){
        $controllersPublicos = [
            'home',
            'login',
            'registro'
        ];

        if (in_array($controller, $controllersPublicos)) {
            return;
        }

        $metodosPublicos = [
            'perfil' => [
                'ver'
            ]
        ];

        $roles = [
            'admin' => ['ADMIN'],
            'lobby' => ['JUGADOR'],
            'partida' => ['JUGADOR']
        ];

        if (isset($metodosPublicos[$controller]) && in_array($method, $metodosPublicos[$controller])) {
            return;
        }
        if (!isset($_SESSION['usuario_id'])) {
            header('Location: /web2-unlam-tp-final/login/show');
            exit();
        }

        if (isset($roles[$controller])) {
            $rolUsuario = $_SESSION['rol'] ?? '';
            if (!in_array($rolUsuario, $roles[$controller])) {
                if ($rolUsuario === 'ADMIN') {
                    header('Location: /web2-unlam-tp-final/admin/show');
                    exit();
                }
                header('Location: /web2-unlam-tp-final/lobby/show');
                exit();
            }
        }
    }

}
