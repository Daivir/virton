<?php
namespace Virton\Session;

/**
 * Class FlashHandler
 * @package Virton\Session
 */
class FlashHandler
{
    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var string
     */
    private $sessionKey = 'flash';

    /**
     * @var string
     */
    private $messages;

    /**
     * FlashHandler constructor
     * @param SessionInterface $session
     */
    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    /**
     * Creates a default flash
     * @param string $message
     * @return void
     */
    public function default(string $message): void
    {
        $this->flash('default', $message);
    }

    /**
     * Creates a success flash
     * @param string $message
     * @return void
     */
    public function success(string $message): void
    {
        $this->flash('success', $message);
    }

    /**
     * Creates a danger flash
     * @param string $message
     * @return void
     */
    public function danger(string $message): void
    {
        $this->flash('danger', $message);
    }

    /**
     * Creates a info flash
     * @param string $message
     * @return void
     */
    public function info(string $message): void
    {
        $this->flash('info', $message);
    }

    /**
     * Creates a warning flash
     * @param string $message
     * @return void
     */
    public function warning(string $message): void
    {
        $this->flash('warning', $message);
    }

    /**
     * Gets the flash by his type
     * @param string $type
     * @return string|null
     */
    public function get(string $type): ?string
    {
        $key = $this->sessionKey;
        if (is_null($this->messages)) {
            $this->messages = $this->session->get($key, []);
            $this->session->delete($key);
        }
        if (array_key_exists($type, $this->messages)) {
            return $this->messages[$type];
        }
        return null;
    }

    /**
     * Creates a flash
     * @param string $type
     * @param string $message
     * @return void
     */
    private function flash(string $type, string $message): void
    {
        $flash = $this->session->get($this->sessionKey, []);
        $flash[$type] = $message;
        $this->session->set($this->sessionKey, $flash);
    }
}
