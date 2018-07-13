<?php
namespace Virton\Renderer;

/**
 * Interface RendererInterface
 * @package Virton\Renderer
 */
interface RendererInterface
{
	/**
	 * This add a path to load views.
	 * @param string $namespace
	 * @param string|null $path
	 * @return void
	 */
	public function addPath(string $namespace, ?string $path = null): void;

	/**
	 * This render a view.
	 * Can be used with namespaces added through addPath().
	 * @example tests/Framework/Renderer/PHPRendererTest.php
	 * @param string $view
	 * @param array $params
	 * @return string
	 */
	public function render(string $view, array $params = []): string;

	/**
	 * This add global variables to all views.
	 * @param string $key
	 * @param mixed $value
	 * @return void
	 */
	public function addGlobal(string $key, $value): void;
}
