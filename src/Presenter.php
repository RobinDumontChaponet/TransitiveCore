<?php

namespace Transitive\Core;

class Presenter
{
    public array $data = [];

    public function __construct()
    {}

    /*
     * Change route
     */
    public function redirect(string $queryURL): void
    {
        unset($this->data);
        throw new BreakFlowException($queryURL);
    }

    /**
     * Get whole data array.
     */
    public function &getData(): array
    {
        return $this->data;
    }

    /**
     * Return true if data array is not empty, false otherwise.
     */
    public function hasData(): bool
    {
        return !empty($this->data);
    }

    /**
     * Set data array.
     */
    public function setData(array &$data): void
    {
        $this->data = $data;
    }

    /**
     * Add data as key/value pair.
     */
    public function addData(int|string $key, mixed $value = null): void
    {
        $this->data[$key] = $value;
    }

    /**
     * Add data as key/value pair.
     * @codeCoverageIgnore
     */
    public function add(int|string $key, mixed $value = null): void
    {
        $this->addData($key, $value);
    }
}
