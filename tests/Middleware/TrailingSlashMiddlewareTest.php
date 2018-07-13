<?php

namespace Tests\Middleware;

use Virton\Middleware\TrailingSlashMiddleware;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;

class TrailingSlashMiddlewareTest extends TestCase
{
    public function testRedirectIfSlash()
    {
        $request = (new ServerRequest('GET', '/test/'));
        $middleware = new TrailingSlashMiddleware();
        /** @var \Psr\Http\Message\ResponseInterface $response*/
        $response = call_user_func_array($middleware, [$request, function () {
        }]);
        $this->assertEquals(['/test'], $response->getHeader('Location'));
        $this->assertEquals(301, $response->getStatusCode());
    }

    public function testCallNextIfNoSlash()
    {
        $request = (new ServerRequest('GET', '/test'));
        $response = new Response();
        /** @var \Psr\Http\Message\ResponseInterface $response*/
        $middleware = new TrailingSlashMiddleware();
        $callback = function () use ($response) {
            return $response;
        };
        $this->assertEquals($response, call_user_func_array($middleware, [$request, $callback]));
    }
}
