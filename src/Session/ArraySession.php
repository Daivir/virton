<?php
namespace Virton\Session;

/**
 * Handle session as an array.
 *
 * Class ArraySession
 * @package Virton\Session
 */
class ArraySession implements SessionInterface
{
    /**
     * @var array
     */
    private $session = [];
    
    /**
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        if (array_key_exists($key, $this->session)) {
            return $this->session[$key];
        }
        return $default;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function set(string $key, $value): void
    {
        $this->session[$key] = $value;
    }

    /**
     * @param string $key
     * @return void
     */
    public function delete(string $key): void
    {
        unset($this->session[$key]);
    }
}
