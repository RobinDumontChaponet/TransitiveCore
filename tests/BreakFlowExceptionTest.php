<?php

declare(strict_types=1);

use Transitive\Core\BreakFlowException;

final class BreakFlowExceptionTest extends PHPUnit\Framework\TestCase
{
    public function testGetQueryUrl()
    {
        $value = 'test';
        $instance = new BreakFlowException($value);

        $this->assertEquals(
            $value,
            $instance->getQueryURL()
        );
    }
}
