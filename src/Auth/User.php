<?php
namespace Virton\Auth;

/**
 * Interface User
 * @package Virton\Auth
 */
interface User
{
    /**
     * @return string
     */
    public function getUsername(): string;
    
    /**
     * @return string[]
     */
    public function getRoles(): array;
}
