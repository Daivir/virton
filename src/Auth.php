<?php
namespace Virton;

use Virton\Auth\User;

/**
 * Interface Auth
 * @package Virton
 */
interface Auth
{
    /**
     * @return User|null
     */
    public function getUser(): ?User;
}
