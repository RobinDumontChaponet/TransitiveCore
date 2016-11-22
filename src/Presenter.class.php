<?php

namespace Transitive\Core;

class Presenter {
	/**
	 * @var array
	 */
	public $data;

	public function __construct()
	{
		$this->data = array();
	}

	/**
	 * @param array $data
	 */
	public function setData(array &$data):void
	{
		$this->data = $data;
	}

	// pretty useless for now…
	public function __debugInfo()
	{
		return array(
			'data' => $this->data,
		);
	}
}
