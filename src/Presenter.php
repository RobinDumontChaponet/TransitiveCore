<?php

namespace Transitive\Core;

class Presenter
{
    /**
     * @var array
     */
    public $data;

    public function __construct()
    {
        $this->data = array();
    }

    /*
     * @param string $queryURL = null
     * @return void
     */
    public function redirect(string $queryURL = null): void
    {
        unset($this->data);
        throw new BreakFlowException($queryURL);
    }

    /**
     * Get whole data array.
     *
     * @return &array
     */
    public function &getData(): array
    {
        return $this->data;
    }

    /**
     * Return true if data array is not empty, false otherwise.
     *
     * @return bool
     */
    public function hasData(): bool
    {
        return !empty($this->data);
    }

    /**
     * Set data array.
     *
     * @param array &$data
     */
    public function setData(array &$data): void
    {
        $this->data = $data;
    }

    /**
     * Add data as key/value pair.
     *
     * @param mixed $key
     * @param mixed $value = null
     */
    public function addData($key, $value = null): void
    {
        $this->data[$key] = $value;
    }

    /**
     * Add data as key/value pair.
     *
     * @param mixed $key
     * @param mixed $value = null
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
