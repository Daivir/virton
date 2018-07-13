<?php
namespace Virton\Middleware;

use Virton\Exception\CsrfInvalidException;
use Virton\Session\SessionInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class CsrfMiddleware
 * @package Virton\Middleware
 */
class CsrfMiddleware implements MiddlewareInterface
{
    /**
     * @var string
     */
    private $formKey;

    /**
     * @var string
     */
    private $sessionKey;

    /**
     * Max number of tokens
     * @var int
     */
    private $limit;

    /**
     * @var \ArrayAccess
     */
    private $session;

    /**
     * CsrfMiddleware constructor.
     * @param $session
     * @param int $limit
     * @param string $formKey
     * @param string $sessionKey
     * @throws \TypeError
     */
    public function __construct(
        &$session,
        int $limit = 50,
        string $formKey = '_csrf',
        string $sessionKey = 'csrf'
    ) {
        $this->validSession($session);
        $this->session = &$session;
        $this->limit = $limit;
        $this->sessionKey = $sessionKey;
        $this->formKey = $formKey;
    }

    /**
     * Process an incoming server request and return a response, optionally delegating
     * response creation to a handler.
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     * @throws \Exception
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (in_array($request->getMethod(), ['POST', 'PUT', 'DELETE', 'OPTIONS'])) {
            $params = $request->getParsedBody() ?: [];
            if (!array_key_exists($this->formKey, $params)) {
                $this->reject();
            } else {
                $csrfList = $this->session[$this->sessionKey] ?? [];
                if (in_array($params[$this->formKey], $csrfList)) {
                    $this->useToken($params[$this->formKey]);
                    return $handler->handle($request);
                } else {
                    $this->reject();
                }
            }
        }
        return $handler->handle($request);
    }

    /**
     * Generate CSRF token.
     * @return string
     * @throws \Exception
     */
    public function generateToken(): string
    {
        $token = bin2hex(random_bytes(16));
        $csrfList = $this->session[$this->sessionKey] ?? [];
        $csrfList[] = $token;
        $this->session[$this->sessionKey] = $csrfList;
        $this->limitTokens();
        return $token;
    }

    /**
     * @throws \Exception
     */
    private function reject(): \Exception
    {
        throw new CsrfInvalidException;
    }

    /**
     * @param string $token
     * @return void
     */
    private function useToken(string $token): void
    {
        $tokens = array_filter($this->session[$this->sessionKey], function ($t) use ($token) {
            return $token !== $t;
        });
        $this->session[$this->sessionKey] = $tokens;
    }

    /**
     * Limit the number of tokens that can be generated
     *
     * @return void
     */
    private function limitTokens(): void
    {
        $tokens = $this->session[$this->sessionKey] ?? [];
        if (count($tokens) > $this->limit) {
            array_shift($tokens);
        }
        $this->session[$this->sessionKey] = $tokens;
    }

    /**
     * @param array|SessionInterface $session
     * @return void
     * @throws \TypeError
     */
    private function validSession($session): void
    {
        if (!is_array($session) && !$session instanceof \ArrayAccess) {
            throw new \TypeError(
                'The session passed to the CSRF middleware is not treatable as an array'
            );
        }
    }

    /**
     * @return string
     */
    public function getFormKey(): string
    {
        return $this->formKey;
    }
}
