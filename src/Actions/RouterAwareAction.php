<?php
namespace Virton\Actions;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;

/**
 * Add methods related to the use of Router
 * Trait RouterAwareAction
 * @package Virton\Actions
 */
trait RouterAwareAction
{
    /**
     * Return a redirect response
     * @param string $path
     * @param array $params
     * @return ResponseInterface
     */
    public function redirect(string $path, array $params = []): ResponseInterface
    {
        $redirectUri = $this->router->generateUri($path, $params);
        return (new Response)
            ->withStatus(301)
            ->withHeader('Location', $redirectUri);
    }
}
