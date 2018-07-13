<?php
namespace Virton\Auth;

use Virton\Auth;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Returns a ForbiddenException exception if a user tries to access a page
 * where the user needs to be logged in.
 *
 * Class LoggedInMiddleware
 * @package Virton\Auth
 */
class LoggedInMiddleware implements MiddlewareInterface
{
    private $auth;

    /**
     * @param Auth $auth
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Process an incoming server request and return a response, optionally delegating
     * response creation to a handler.
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $user = $this->auth->getUser();
        if (is_null($user)) {
            throw new ForbiddenException;
        }
        return $handler->handle($request->withAttribute('user', $user));
    }
}
