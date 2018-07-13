<?php
namespace Virton\Twig;

use Virton\Middleware\CsrfMiddleware;

/**
 * Implements extension about Cross-Site Request Forgery security
 *
 * Class CsrfExtension
 * @package Virton\Twig
 */
class CsrfExtension extends \Twig_Extension
{
    /**
     * @var CsrfMiddleware
     */
    private $csrfMiddleware;

    /**
     * CsrfExtension constructor.
     * @param CsrfMiddleware $csrfMiddleware
     */
    public function __construct(CsrfMiddleware $csrfMiddleware)
    {
        $this->csrfMiddleware = $csrfMiddleware;
    }

    /**
     * @return array
     */
    public function getFunctions(): array
    {
        return [
            new \Twig_SimpleFunction('csrf_input', [$this, 'csrfInput'], ['is_safe' => ['html']])
        ];
    }

    /**
     * Cross-Site Request Forgery security input.
     * @return string
     * @throws \Exception
     */
    public function csrfInput(): string
    {
        return "<input 
            type=\"hidden\" 
            name=\"{$this->csrfMiddleware->getFormKey()}\" 
            value=\"{$this->csrfMiddleware->generateToken()}\"
        />";
    }
}
