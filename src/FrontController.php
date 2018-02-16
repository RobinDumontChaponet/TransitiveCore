<?php

namespace Transitive\Core;

interface FrontController
{
    /**
     * Get presenter & view buffer (if obClean is enabled).
     *
     * @return string
     */
    public function getObContent(): string;

    /**
     * Execute routers for query and return route if any.
     *
     * @return Route
     *
     * @param string $queryUrl = null
     */
    public function execute(string $queryURL = null): ?Route;

    /**
     * Get all routers.
     *
     * @return array
     */
    public function getRouters(): array;

    /**
     * Set routers list, replacing any previously set Router.
     *
     * @param array $routers
     */
    public function setRouters(array $routers): void;

    /**
     * Add specified router.
     *
     * @param Router $router
     */
    public function addRouter(Router $router): void;

    /**
     * Remove specified router
     * return true at success and false otherwise.
     *
     * @return bool
     *
     * @param Router $router
     */
    public function removeRouter(Router $router): bool;

    /**
     * Return current route.
     *
     * @return Route
     */
    public function getRoute(): ?Route;

    /**
     * Return processed content from current route.
     *
     * @return string
     *
     * @param string $contentType = null
     */
    public function getContent(string $contentType = null): string;
}
