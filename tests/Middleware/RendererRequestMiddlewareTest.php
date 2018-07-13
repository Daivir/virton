<?php
namespace Tests\Middleware;

use Virton\Middleware\RendererRequestMiddleware;
use Virton\Renderer\RendererInterface;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use Psr\Http\Server\RequestHandlerInterface;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Psr\Http\Message\ServerRequestInterface;

class RendererRequestMiddlewareTest extends TestCase
{
    /**
     * @var \Prophecy\Prophecy\ObjectProphecy
     */
    private $renderer;
    /**
     * @var RendererRequestMiddleware
     */
    private $middleware;
    /**
     * @var \Prophecy\Prophecy\ObjectProphecy
     */
    private $handler;

    protected function setUp()
    {
        $this->renderer = $this->prophesize(RendererInterface::class);
        $this->handler = $this->prophesize(RequestHandlerInterface::class);
        $this->handler->handle(Argument::type(ServerRequestInterface::class))
            ->willReturn(new Response());
        $this->handler = $this->handler->reveal();
        $this->middleware = new RendererRequestMiddleware($this->renderer->reveal());
    }

    public function testAddGlobalDomain()
    {
        $this->renderer->addGlobal('domain', 'http://daivir.fr')->shouldBeCalled();
        $this->renderer->addGlobal('domain', 'http://localhost:3000')->shouldBeCalled();
        $this->renderer->addGlobal('domain', 'https://localhost')->shouldBeCalled();
        $this->middleware->process(new ServerRequest('GET', 'http://daivir.fr/blog/test'), $this->handler);
        $this->middleware->process(new ServerRequest('GET', 'http://localhost:3000/blog/test'), $this->handler);
        $this->middleware->process(new ServerRequest('GET', 'https://localhost/blog/test'), $this->handler);
    }
}
