<?php

namespace Transitive\Core;

class ListRegexRouter extends ListRouter implements Router
{
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
}
