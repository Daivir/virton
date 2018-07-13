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
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __invoke(ContainerInterface $container): Router
    {
        $cache = null;
        if ($container->get('env') === 'production') {
            $cache = 'tmp/routes';
        }
        return new Router($cache);
    }
}
