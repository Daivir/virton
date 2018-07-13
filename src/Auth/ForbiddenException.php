<?php
namespace Virton\Auth;

class ForbiddenException extends \Exception
{
    public function __construct(string $message = 'You cannot access this page', int $code = 403)
    {
        parent::__construct($message, $code);
    }
}
