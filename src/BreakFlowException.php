<?php

namespace Transitive\Core;

class BreakFlowException extends \Exception
{
    private $queryURL;

    public function __construct(string $queryURL = null) {
        $this->queryURL = $queryURL;

        parent::__construct();
    }

    public function getQueryURL(): ?string
    {
        return $this->queryURL;
    }
}