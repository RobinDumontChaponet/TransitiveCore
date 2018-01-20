<?php

namespace Transitive\Core;

class Presenter
{
    /**
     * @var array
     */
    public $data;

    /**
     * __construct function.
     *
     * @access public
     * @return void
     */
    public function __construct()
    {
        $this->data = array();
    }

	public function execute(string $queryURL = null)
	{
		throw new BreakFlowException($queryURL);
	}

	/**
	 * Get whole data array
	 *
	 * @access public
	 * @return &array
	 */
    public function &getData(): array
    {
        return $this->data;
    }

    /**
	 * Set data array
	 *
	 * @access public
     * @param array &$data
	 * @return void
     */
    public function setData(array &$data): void
    {
        $this->data = $data;
    }

    /**
	 * Add data as key/value pair
	 *
	 * @access public
     * @param mixed $key
     * @param mixed $value = null
	 * @return void
     */
    public function addData($key, $value = null): void
    {
        $this->data[$key] = $value;
    }

	/**
	 * Add data as key/value pair
	 *
	 * @access public
     * @param mixed $key
     * @param mixed $value = null
	 * @return void
     * @codeCoverageIgnore
     */
    public function add($key, $value = null): void
    {
        $this->addData($key, $value);
    }

	/**
	 * @codeCoverageIgnore
	 */
    public function __debugInfo()
    {
        return array(
            'data' => $this->data,
        );
    }
}
