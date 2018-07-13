<?php
namespace Virton\Response;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Class RedirectResponse
 * @package Virton\Response
 */
class RedirectBackResponse extends RedirectResponse
{
    /**
     * RedirectResponse constructor
     * @param ServerRequestInterface $request
     */
    public function __construct(ServerRequestInterface $request)
    {
        parent::__construct($request->getServerParams()['HTTP_REFERER'] ?? '/');
    }
}
