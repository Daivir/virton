<?php

namespace Tests\Middleware;

use Virton\Middleware\RouterMiddleware;
use Virton\Router;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Virton\Router\Route;
use Psr\Http\Message\ServerRequestInterface;

class RouterMiddlewareTest extends TestCase
{
    public function makeMiddleware(?Route $route)
    {
        $router = $this->getMockBuilder(Router::class)->getMock();
        $router->method('match')->willReturn($route);
        return new RouterMiddleware($router);
    }

    public function testPassParameters()
    {
        $route = new Route('test', 'trim', ['id' => 2]);
        $middleware = $this->makeMiddleware($route);
        $test = function (ServerRequestInterface $request) use ($route) {
            $this->assertEquals(2, $request->getAttribute('id'));
            $this->assertEquals($route, $request->getAttribute(get_class($route)));
            return new Response();
        };
        call_user_func_array($middleware, [new ServerRequest('GET', '/test'), $test]);
    }

    public function testCallNext()
    {
        $request = null;
        $middleware = $this->makeMiddleware($request);
        $response = new Response();
        $test = function () use ($response) {
            return $response;
        };
        $this->assertEquals($response, call_user_func_array($middleware, [
            new ServerRequest('GET', '/test'),
            $test
        ]));
    }
}
