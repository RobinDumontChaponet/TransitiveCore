<?php

namespace Transitive\Core;

interface Router
{
    public function execute(string $pattern, string $method): ?Route;
}
