<?php
namespace Virton\Auth;

use Virton\Auth;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class RoleMiddleware implements MiddlewareInterface
{
    /**
     * @var Auth
     */
    private $auth;
    /**
     * @var string
     */
    private $role;

    public function __construct(Auth $auth, string $role)
    {

        $this->auth = $auth;
        $this->role = $role;
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
        $user = $this->auth->getUser();
        if (is_null($user) || !in_array($this->role, $user->getRoles())) {
            throw new ForbiddenException;
        }
        return $handler->handle($request);
    }
}
