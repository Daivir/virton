<?php

namespace Tests\Middleware;

use Virton\Middleware\NotFoundMiddleware;
use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;

class NotFoundMiddlewareTest extends TestCase
{
    public function testSendNotFound()
    {
        $request = (new ServerRequest('GET', '/test'));
        $middleware = new NotFoundMiddleware();
        /** @var \Psr\Http\Message\ResponseInterface $response */
        $response = call_user_func_array($middleware, [$request, function () {
        }]);
        $this->assertEquals(404, $response->getStatusCode());
    }
}
