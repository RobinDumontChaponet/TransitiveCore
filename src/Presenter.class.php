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
	 *
	 */
	public function &getData():array
	{
		return $this->data;
	}

	/**
	 * @param array $data
	 */
	public function setData(array &$data):void
	{
		$this->data = $data;
	}

	/**
	 * @param array $data
	 */
	public function addData($key, $value=null):void
	{
		$this->data[$key] = $value;
	}
	public function add($key, $value=null):void
	{
		$this->addData($key, $value);
	}

	// pretty useless for now…
	public function __debugInfo()
	{
		return array(
			'data' => $this->data
		);
	}
}
