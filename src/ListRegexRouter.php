<?php

namespace Transitive\Core;

class ListRegexRouter implements Router
{
    /**
     * @var array Route
     */
    public $routes;
    private $prefix;
    private $exposedVariables;
    private $defaultViewClassName;

    public function __construct(array $routes, string $prefix = null, array $exposedVariables = []) {
        $this->setRoutes($routes);
        $this->setPrefix($prefix);
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

        foreach ($this->routes as $key => $tmp)
            if (preg_match('@'.$key.'@', $pattern, $matches)) {
                unset($matches[key($matches)]);
                $_GET += $matches;

                if(!is_array($tmp)) {
                    $route = $tmp;
                    break;
                } elseif(isset($tmp[$method])) {
                    $route = $tmp[$method];
                    break;
                }
            }

        if($route) {
            if(!$route->hasExposedVariables())
                $route->setExposedVariables($this->exposedVariables);
            if(!$route->hasPrefix())
                $route->setPrefix($this->prefix);
            if(!$route->hasDefaultViewClassName())
                $route->setDefaultViewClassName($this->defaultViewClassName);
        }

        return $route;
    }

    public function setExposedVariables(array $exposedVariables = []): void
    {
        $this->exposedVariables = $exposedVariables;
    }

    public function setPrefix(string $prefix = null): void
    {
        $this->prefix = $prefix;
    }

    public function setDefaultViewClassName(string $defaultViewClassName = null): void
    {
        $this->defaultViewClassName = $defaultViewClassName;
    }

    public function hasDefaultViewClassName(): bool
    {
        return !empty($this->defaultViewClassName);
    }
}
