<?php
namespace Virton\Router;

/**
 * Matched routes object.
 *
 * Class Route
 * @package Virton\Router
 */
class Route
{
	/**
	 * @var string
	 */
	private $name;

	/**
	 * @var callable
	 */
	private $callback;

	/**
	 * @var array
	 */
	private $params;

	/**
	 * Route constructor
	 * @param string $name
	 * @param string|callable $callback
	 * @param array $params
	 */
	public function __construct(string $name, $callback, array $params)
	{
		$this->name = $name;
		$this->callback = $callback;
		$this->params = $params;
	}

	/**
	 * Get the URL name.
	 * @return string
	 */
	public function getName(): string
	{
		return $this->name;
	}

	/**
	 * Get the URL callback.
	 * @return string|callable
	 */
	public function getCallback()
	{
		return $this->callback;
	}

	/**
	 * Get the URL parameters.
	 * @return string[]
	 */
	public function getParams(): array
	{
		return $this->params;
	}
}
