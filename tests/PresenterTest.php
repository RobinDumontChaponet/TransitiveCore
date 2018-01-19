<?php

declare(strict_types=1);

use Transitive\Core\Presenter;

final class PresenterTest extends PHPUnit\Framework\TestCase
{
    public function testConstruct()
    {
		$instance = new Presenter();

        $this->assertInternalType(
			'array',
			$instance->data
        );
    }

    public function testGetData()
    {
		$instance = new Presenter();

        $this->assertInternalType(
			'array',
			$instance->getData()
        );
    }

    public function testSetData()
    {
		$instance = new Presenter();
		$value = ['key'=>'value'];
		$instance->setData($value);

        $this->assertEquals(
			$value,
			$instance->data
        );
    }

    public function testAddData()
    {
		$instance = new Presenter();
		$instance->addData('key', 'value');

        $this->assertEquals(
			['key'=>'value'],
			$instance->data
        );
    }
}
