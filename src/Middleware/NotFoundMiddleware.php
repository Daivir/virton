<?php
namespace Virton\Middleware;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class NotFoundMiddleware
 * @package Virton\Middleware
 */
class NotFoundMiddleware
{
    /**
     * @param ServerRequestInterface $request
     * @param callable $next
     * @return Response
     */
    public function __invoke(ServerRequestInterface $request, callable $next): Response
    {
        return new Response(404, [], "Error 404: Not Found");
    }
}
