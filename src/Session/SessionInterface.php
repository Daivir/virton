<?php
namespace Virton\Session;

/**
 * Interface SessionInterface
 * @package Virton\Session
 */
interface SessionInterface
{
    /**
     * Retrieves a session.
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null);

    /**
     * Adds a session.
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function set(string $key, $value): void;

    /**
     * Deletes a session.
     * @param string $key
     * @return void
     */
    public function delete(string $key): void;
}
