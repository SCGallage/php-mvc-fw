<?php

namespace core_fw;

use core_fw\Authentication\AuthenticatorModule;
use core_fw\di_container\Container;
use core_fw\Sessions\SessionManagement;

class Application
{

    public Router $router;
    public Request $request;
    public static string $ROOT_DIR;
    public Response $response;
    public Controller $controller;
    public Database $database;
    public Container $container;
    public DatabaseService $databaseService;
    public AuthenticatorModule $auth;
    public SessionManagement $management;
    public static Application $app;
    //private DatabaseService $databaseService;

    public function __construct($rootPath, array $config)
    {
        self::$ROOT_DIR = $rootPath;
        self::$app = $this;
        $this->request = new Request();
        $this->response = new Response();
        $this->container = new Container();
        $this->router = new Router($this->request, $this->response);
        $this->database = new Database($config);
        $this->management = new SessionManagement($this->database->getConnection());
        $this->databaseService = new DatabaseService($this->database);
        //$this->auth = $this->container->get('core_fw\Authentication\AuthenticatorModule');
    }

    /**
     * resolve requests
     */
    public function run()
    {
        echo $this->router->resolve();
    }

    /**
     * @return Controller
     */
    public function getController(): Controller
    {
        return $this->controller;
    }

    /**
     * @param Controller $controller
     */
    public function setController(Controller $controller): void
    {
        $this->controller = $controller;
    }
}