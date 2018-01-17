<?php

namespace Transitive\Core;

interface FrontController
{
	/**
     * @return string
     */
    public function getObContent(): string;

	/**
     * @return Route
     * @param string $queryUrl = null
     */
    public function execute(string $queryURL = null): ?Route;

    /**
     * @return array
     */
    public function getRouters(): array;

    /**
     * @param array $routers
     */
    public function setRouters(array $routers): void;

    /**
     * @param Router $router
     */
    public function addRouter(Router $router): void;

    /**
     * @return bool
     * @param Router $router
     */
    public function removeRouter(Router $router): bool;

    /**
     * @return Route
     */
    public function getRoute(): ?Route;

	/**
	 * Get calculated content.
     * @return string
     * @param string $contentType = null
     */
    public function getContent(string $contentType = null): string;
}
