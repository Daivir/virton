<?php
/**
 * Created by PhpStorm.
 * User: dupont
 * Date: 26/11/2017
 * Time: 20:44
 */

namespace Tests\Middleware;

use Virton\Middleware\DispatcherMiddleware;
use Virton\Router\Route;
use GuzzleHttp\Psr7\ServerRequest;
use Psr\Http\Server\RequestHandlerInterface;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;

class DispatcherMiddlewareTest extends TestCase
{
    public function testDispatchCallback()
    {
        $callback = function () {
            return 'TEST';
        };
        $route = new Route('test', $callback, []);
        $request = (new ServerRequest('GET', '/test'))
            ->withAttribute(Route::class, $route);
        $container = $this->getMockBuilder(ContainerInterface::class)->getMock();
        $dispatcher = new DispatcherMiddleware($container);
        $response = $dispatcher->process($request, $this->getMockBuilder(RequestHandlerInterface::class)->getMock());
        $this->assertEquals('TEST', (string)$response->getBody());
    }

    public function testCallNextIfNotRoutes()
    {
        $response = $this->getMockBuilder(ResponseInterface::class)->getMock();
        $handler = $this->getMockBuilder(RequestHandlerInterface::class)->getMock();
        $container = $this->getMockBuilder(ContainerInterface::class)->getMock();

        $handler->expects($this->once())->method('handle')->willReturn($response);

        $request = new ServerRequest('GET', '/test');
        $dispatcher = new DispatcherMiddleware($container);
        $this->assertEquals($response, $dispatcher->process($request, $handler));
    }
}
