<?php
namespace Virton\Router;

use Virton\Router;
use Psr\Container\ContainerInterface;

/**
 * Class RouterFactory
 * @package Virton\Router
 */
class RouterFactory
{
    /**
     * @param ContainerInterface $container
     * @return Router
     */
    public function __invoke(ContainerInterface $container): Router
    {
        $cache = null;
        if ($container->get('env') === 'production') {
            $cache = $container->get('paths.cache');
        }
        return new Router($container, $cache);
    }
}
