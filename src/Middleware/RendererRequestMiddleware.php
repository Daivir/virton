<?php
namespace Virton\Middleware;

use Virton\Renderer\RendererInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class RendererRequestMiddleware implements MiddlewareInterface
{
    /**
     * @var RendererInterface
     */
    private $renderer;

    public function __construct(RendererInterface $renderer)
    {
        $this->renderer = $renderer;
    }

    /**
     * Process an incoming server request and return a response, optionally delegating
     * response creation to a handler.
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $uri = $request->getUri();
        $port = $uri->getPort() ? ':' . $uri->getPort() : '';
        $domain = sprintf('%s://%s%s', $uri->getScheme(), $uri->getHost(), $port);
        $this->renderer->addGlobal('domain', $domain);
        return $handler->handle($request);
    }
}
