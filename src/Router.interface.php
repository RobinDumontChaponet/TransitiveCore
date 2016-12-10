<?php

namespace Transitive\Core;

interface Router {

    public function __construct(array $routes);

    public function getRoutes():array;

    /**
     * @param array $routes
     */
    public function setRoutes(array $routes):void;

    /**
     * @param Route $route
     */
    public function addRoute(Route $route):void;

    public function removeRoute(Route $route):bool;

    public function execute($query);
}
