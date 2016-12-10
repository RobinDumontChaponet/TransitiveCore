<?php

namespace Transitive\Core;

interface Router {
    public function execute(string $query):?Route;
}
