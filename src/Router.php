<?php

namespace Transitive\Core;

interface Router
{
    public function setDefaultViewClassName(string $defaultViewClassName = null): void;

    public function hasDefaultViewClassName(): bool;

    public function execute(string $pattern, string $method): ?Route;

    public function getRoutes(): array;
}
