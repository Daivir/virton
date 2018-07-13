<?php
namespace Virton\Renderer;

use Psr\Container\ContainerInterface;
use Virton\Router\RouterTwigExtension;
use Twig\Extension\DebugExtension;

/**
 * Class TwigRendererFactory
 * @package Virton\Renderer
 */
class TwigRendererFactory
{
	/**
	 * @param ContainerInterface $container
	 * @return TwigRenderer
	 * @throws \Psr\Container\ContainerExceptionInterface
	 * @throws \Psr\Container\NotFoundExceptionInterface
	 */
	public function __invoke(ContainerInterface $container): TwigRenderer
	{
		$debug = $container->get('env') !== "production";
		$viewPath = $container->get('views.path');
		$loader = new \Twig_Loader_Filesystem($viewPath);
		$twig = new \Twig_Environment($loader, [
			'debug' => $debug,
			'cache' => $debug ? false : 'tmp/views',
			'auto_reload' => $debug
		]);
		$twig->addExtension(new DebugExtension);
		if ($container->has('twig.extensions')) {
			foreach ($container->get('twig.extensions') as $extension) {
				$twig->addExtension($extension);
			}
		}
		return new TwigRenderer($twig);
	}
}
