<?php

namespace Transitive\Core;

/**
 * Exception break flow and send request to FrontController when thrown from presenter.
 */
class BreakFlowException extends \RuntimeException
{
    public function __construct(
		private string $queryURL,
	) {
        parent::__construct();
    }

    public function getQueryURL(): string
    {
        return $this->queryURL;
    }
}
