<?php
namespace Virton\Renderer;

/**
 * Class PHPRenderer
 * @package Virton\Renderer
 */
class PHPRenderer implements RendererInterface
{
	const DEFAULT_NAMESPACE = '__MAIN';

	/**
	 * Variables globaly accessible for all views.
	 * @var array
	 */
	private $globals = [];

	/**
	 * @var string[]
	 */
	private $paths = [];

	/**
	 * PHPRenderer constructor
	 * @param string|null $defaultPath
	 */
	public function __construct(?string $defaultPath = null)
	{
		if (!is_null($defaultPath)) {
			$this->addPath($defaultPath);
		}
	}

	/**
	 * This add a path to load views.
	 * @param string $namespace
	 * @param string|null $path
	 * @return void
	 */
	public function addPath(string $namespace, ?string $path = null): void
	{
		if (is_null($path)) {
			$this->paths[self::DEFAULT_NAMESPACE] = $namespace;
		} else {
			$this->paths[$namespace] = $path;
		}
	}

	/**
	 * This render a view.
	 * Can be used with namespaces added through addPath().
	 * @example tests/Framework/Renderer/PHPRendererTest.php
	 * @param string $view
	 * @param array $params
	 * @return string
	 */
	public function render(string $view, array $params = []): string
	{
		if ($this->hasNamespace($view)) {
			$path = $this->replaceNamespace($view) . '.php';
		} else {
			$path = $this->paths[self::DEFAULT_NAMESPACE] . DIRECTORY_SEPARATOR . "$view.php";
		}
		ob_start();
		$renderer = $this;
		extract($this->globals);
		extract($params);
		require $path;
		$content = ob_get_clean();
		return $content;
	}

	/**
	 * This add global variables to all views.
	 * @param string $key
	 * @param mixed $value
	 * @return void
	 */
	public function addGlobal(string $key, $value): void
	{
		$this->globals[$key] = $value;
	}

	/**
	 * @param string $view
	 * @return bool
	 */
	private function hasNamespace(string $view): bool
	{
		return ($view[0] === "@");
	}

	/**
	 * @param string $view
	 * @return string
	 */
	private function getNamespace(string $view): string
	{
		return substr($view, 1, strpos($view, '/') - 1);
	}

	/**
	 * @param string $view
	 * @return string
	 */
	private function replaceNamespace(string $view): string
	{
		$namespace = $this->getNamespace($view);
		return str_replace("@$namespace", $this->paths[$namespace], $view);
	}
}
