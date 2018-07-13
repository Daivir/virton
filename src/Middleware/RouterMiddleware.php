<?php
namespace Virton\Middleware;

use Virton\Router;
use Psr\Http\Message\ServerRequestInterface;
use GuzzleHttp\Psr7\Response;

/**
 * Class RouterMiddleware
 * @package Virton\Middleware
 */
class RouterMiddleware
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
     * @param ServerRequestInterface $request
     * @param callable $next
     * @return Response
     */
    public function __invoke(ServerRequestInterface $request, callable $next): Response
    {
		$route = $this->router->match($request);
		if (is_null($route)) {
			return $next($request);
		}
		$params = $route->getParams();
	    $request = array_reduce(array_keys($params), function (ServerRequestInterface $request, $key) use ($params) {
		    return $request->withAttribute($key, $params[$key]);
		}, $request);
	    $request = $request->withAttribute(get_class($route), $route);
        return $next($request);
    }
}
