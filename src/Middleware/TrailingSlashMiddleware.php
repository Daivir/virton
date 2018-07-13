<?php
namespace Virton\Middleware;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class TrailingSlashMiddleware
 * @package Virton\Middleware
 */
class TrailingSlashMiddleware
{
    /**
     * @param ServerRequestInterface $request
     * @param callable $next
     * @return mixed
     */
    public function __invoke(ServerRequestInterface $request, callable $next)
    {
        $uri = $request->getUri()->getPath();
        if (!empty($uri) && $uri[-1] === "/" && $uri !== '/') {
			return (new Response)
				->withStatus(301)
				->withHeader('Location', substr($uri, 0, -1));
        }
        return $next($request);
    }
}
