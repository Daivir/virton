<?php
namespace Virton\Middleware;

use Virton\Router;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class RouterMiddleware
 * @package Virton\Middleware
 */
class RouterMiddleware implements MiddlewareInterface
{
    /**
     * @var Router
     */
    private $router;

    /**
     * RouterMiddleware constructor
     *
     * @param Router $router
     */
    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * Process an incoming server request and return a response, optionally delegating
     * response creation to a handler.
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws \Exception
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $route = $this->router->match($request);
		if (is_null($route)) {
			return $handler->handle($request);
		}
		$params = $route->getParams();
	    $request = array_reduce(array_keys($params), function (ServerRequestInterface $request, $key) use ($params) {
		    return $request->withAttribute($key, $params[$key]);
		}, $request);
	    $request = $request->withAttribute(get_class($route), $route);
        return $handler->handle($request);
    }
}
