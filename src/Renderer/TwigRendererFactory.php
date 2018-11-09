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
		$loader = new \Twig_Loader_Filesystem($container->get('paths.views'));
		$twig = new \Twig_Environment($loader, [
			'debug' => $debug,
			'cache' => $debug ? false : $container->get('paths.cache'),
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
