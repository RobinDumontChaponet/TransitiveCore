<?php

namespace Transitive\Core;

/*
if(!is_file($this->request->getPresenterPath())) {
            http_response_code(404);
            $_SERVER['REDIRECT_STATUS'] = 404;

            $this->request->setPresenterPath('genericHttpErrorHandler.presenter.php');
            if(!is_file(self::$viewIncludePath.'genericHttpErrorHandler.view.php'))
                $this->request->setViewPath('');
            else
                $this->request->setViewPath(self::$viewIncludePath.'genericHttpErrorHandler.view.php');
		}
*/

class Router
{
    /**
     * @var array Route
     */
    public $routes;

    /**
     * @var string constant
     */
    public $type;

    public function __construct() {}

    public function getRoutes():array
    {
        // TODO: implement here
    }

    /**
     * @param array $routes
     */
    public function setRoutes(array $routes):void
    {
        // TODO: implement here
    }

    /**
     * @param Route $route
     */
    public function addRoute(Route $route):void
    {
        // TODO: implement here
    }

    public function removeRoute(Route $route):bool
    {
        // TODO: implement here
    }

    public function getType():string
    {
        // TODO: implement here
    }

    /**
     * @param string $type (constant in fact)
     */
    public function setType(string $type):void
    {
        // TODO: implement here
    }
}
