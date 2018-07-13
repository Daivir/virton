<?php
namespace Virton\Middleware;

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class MethodMiddleware
 * @package Virton\Middleware
 */
class MethodMiddleware implements MiddlewareInterface
{
	/**
	 * Process an incoming server request and return a response, optionally delegating
	 * response creation to a handler.
	 *
	 * @param ServerRequestInterface $request
	 * @param RequestHandlerInterface $next
	 * @return ResponseInterface
	 */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $next): ResponseInterface
    {
        $parsedBody = $request->getParsedBody();
		if (array_key_exists('_method', $parsedBody) &&
			in_array($parsedBody['_method'], ['DELETE', 'PUT', 'OPTIONS'])
		) {
			$request = $request->withMethod($parsedBody['_method']);
        }
        return $next->handle($request);
    }
}
