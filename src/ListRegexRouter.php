<?php

namespace Transitive\Core;

class ListRegexRouter implements Router
{
    /**
     * @var array Route
     */
    public $routes;

    public function __construct(array $routes) {
        $this->setRoutes($routes);
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

        foreach ($this->routes as $key => $route)
            if (preg_match('@'.$key.'@', $pattern, $matches)) {
                unset($matches[key($matches)]);
                $_GET += $matches;

                if(!is_array($route))
                    return $route;
                elseif(isset($route[$method]))
                    return $route[$method];
            }

        return null;
    }
}
