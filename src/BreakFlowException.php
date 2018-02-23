<?php

namespace Transitive\Core;


/**
 * Exception break flow and send request to FrontController when thrown from presenter.
 */
class BreakFlowException extends \RuntimeException
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
