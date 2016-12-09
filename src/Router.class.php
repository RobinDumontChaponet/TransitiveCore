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

class Router {
    /**
     * @var array Route
     */
    public $routes;

    public function __construct(array $routes) {
		$this->setRoutes($routes);
    }

    public function getRoutes():array
    {
        return $this->routes;
    }

    /**
     * @param array $routes
     */
    public function setRoutes(array $routes):void
    {
        $this->routes = $routes;
    }

    /**
     * @param Route $route
     */
    public function addRoute(Route $route):void
    {
        $this->routes[] = $route;
    }

    public function removeRoute(Route $route):bool
    {
        // TODO: implement here
    }

    public function execute($query)
    {
		if(isset($this->routes[$query]))
		    return $this->routes[$query];
    }
}
