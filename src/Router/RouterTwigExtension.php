<?php
namespace Virton\Router;

use Virton\Router;

/**
 * Adds extra functions to Twig.
 *
 * Class RouterTwigExtension
 * @package Virton\Router
 */
class RouterTwigExtension extends \Twig_Extension
{
	/**
	 * @var Router
	 */
	private $router;

    /**
     * RouterTwigExtension constructor.
     * @param Router $router
     */
	public function __construct(Router $router)
	{
		$this->router = $router;
	}

	/**
	 * Allow to register functions into a Twig extension.
	 * @return \Twig_Extension[]
	 */
	public function getFunctions()
	{
		return [
			new \Twig_SimpleFunction('path', [$this, 'pathFor']),
			new \Twig_SimpleFunction('is_subpath', [$this, 'isSubPath'])
		];
	}

	/**
	 * Generate path from path name.
	 * @param string $path
	 * @param array $params
	 * @return string
	 */
	public function pathFor(string $path, array $params = []): string
	{
		return $this->router->generateUri($path, $params);
	}

	/**
	 * Compares the attribute path to the current path.
	 * @param string $path
	 * @param array $params
	 * @return bool
	 */
	public function isSubPath(string $path, array $params = []): bool
	{
		$currentUri = $_SERVER['REDIRECT_URL'] ?? '/';
		$expectedUri = $this->router->generateUri($path, $params);
		return $currentUri === $expectedUri;
	}
}
