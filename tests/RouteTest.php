<?php

declare(strict_types=1);

use Transitive\Core\Route;
use Transitive\Core\Presenter;
use Transitive\Core\BasicView;

final class RouteTest extends PHPUnit\Framework\TestCase
{
    public function testConstruct()
    {
	    $presenter = new Presenter();
	    $view = new BasicView();
        $instance = new Route($presenter, $view);

        $this->assertEquals(
			$instance->presenter,
            $presenter
        );
        $this->assertEquals(
			$instance->view,
            $view
        );
    }
}
