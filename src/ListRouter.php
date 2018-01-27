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

class ListRouter implements Router
{
    /**
     * @var array Route
     */
    public $routes;
    private $exposedVariables;

    public function __construct(array $routes, array $exposedVariables = []) {
        $this->setRoutes($routes);
        $this->exposedVariables = $exposedVariables;
    }

    public function getRoutes(): array
    {
        return $this->routes;
    }

    /**
     * @param array $routes
     */
    public function setRoutes(array $routes): void
    {
        $this->routes = $routes;
    }

    /**
     * @param Route $route
     */
    public function addRoute(string $pattern, Route $route, string $method = 'all'): void
    {
        if(isset($this->routes[$pattern]))
            if(!is_array($this->routes[$pattern]))
                $this->routes[$pattern] = array('all' => $this->routes[$pattern]);

        $this->routes[$pattern][$method] = $route;
    }

    public function removeRoute(string $pattern, string $method = 'all'): void
    {
        if(isset($this->routes[$pattern])) {
            if(!is_array($this->routes[$pattern]))
                unset($this->routes[$pattern]);
            elseif(isset($this->routes[$pattern][$method]))
                unset($this->routes[$pattern][$method]);
        }
    }

    public function execute(string $pattern, string $method = 'all'): ?Route
    {
        $pattern = rtrim($pattern, '/');
        $route = null;

        if(isset($this->routes[$pattern]))
            if(!is_array($this->routes[$pattern]))
                $route = $this->routes[$pattern];
            elseif(isset($this->routes[$pattern][$method]))
                $route = $this->routes[$pattern][$method];

        if($route && !$route->hasExposedVariables())
            $route->setExposedVariables($this->exposedVariables);

        return $route;
    }

    public function setExposedVariables(array $exposedVariables = []): void
    {
        $this->exposedVariables = $exposedVariables;
    }
}
