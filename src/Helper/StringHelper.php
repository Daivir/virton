<?php
namespace Virton\Helper;

/**
 * Class StringHelper
 * @package Virton\Helper
 */
class StringHelper
{
    /**
     * Camelize a string
     *
     * @param string $string
     * @return string
     */
    public static function camelize(string $string): string
    {
        return lcfirst(
            join('', array_map('ucfirst', explode('_', $string)))
        );
    }
}
