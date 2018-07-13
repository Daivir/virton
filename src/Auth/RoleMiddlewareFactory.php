<?php
namespace Virton\Auth;

use Virton\Auth;

class RoleMiddlewareFactory
{
    /**
     * @var Auth
     */
    private $auth;

    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    public function makeRole($role): RoleMiddleware
    {
        return new RoleMiddleware($this->auth, $role);
    }
}
