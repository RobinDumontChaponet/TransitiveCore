<?php

namespace Transitive\Core;

class Presenter
{
	public array $data = [];
	private array $namesToBinds = [];

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

	public function expose(mixed &$value): void
	{
		$trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
		$file  = $trace[0]['file'] ?? null;
		$line  = $trace[0]['line'] ?? null;

		if (!$file || !$line) {
			throw new \RuntimeException('Unable to determine call site.');
		}

		$sourceLine = file($file)[$line - 1] ?? null;

		if ($sourceLine === null) {
			throw new \RuntimeException('Unable to read source line.');
		}

		if (!preg_match('/->expose\(\s*(\$\w+)\s*\)/', $sourceLine, $m)) {
			throw new \RuntimeException('Unable to infer variable name.');
		}

		$this->add(substr($m[1], 1), $value);
	}

	public function bind(string ...$names): void
	{
		$this->namesToBinds += $names;
	}

	public function doBind(array $scope): void
	{
		foreach ($this->namesToBinds as $name) {
			if (!array_key_exists($name, $scope)) {
				throw new \RuntimeException('Unknown variable: '.$name);
			}

			$this->add($name, $scope[$name]);
		}
	}
}
