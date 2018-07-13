<?php
namespace Virton\Renderer;

/**
 * Handle twig files.
 *
 * Class TwigRenderer
 * @package Virton\Renderer
 */
class TwigRenderer implements RendererInterface
{
	/**
	 * @var \Twig_Environment
	 */
	private $twig;

	/**
	 * TwigRenderer constructor
	 * @param \Twig_Environment $twig
	 */
	public function __construct(\Twig_Environment $twig)
	{
		$this->twig = $twig;
	}

	/**
	 * This add a path to load views.
	 * @param string $namespace
	 * @param string|null $path
	 * @return void
	 */
	public function addPath(string $namespace, ?string $path = null): void
	{
		$this->twig->getLoader()->addPath($path, $namespace);
	}

    /**
     * This render a view.
     * Can be used with namespaces added through addPath().
     * @param string $view
     * @param array $params
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
	public function render(string $view, array $params = []): string
	{
		return $this->twig->render("$view.twig", $params);
	}

	/**
	 * This add global variables to all views.
	 * @param string $key
	 * @param mixed $value
	 * @return void
	 */
	public function addGlobal(string $key, $value): void
	{
		$this->twig->addGlobal($key, $value);
	}

	/**
	 * @return \Twig_Environment
	 */
	public function getTwig(): \Twig_Environment
	{
		return $this->twig;
	}
}
