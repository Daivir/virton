<?php
namespace Virton\Database;

/**
 * Hydrates entities
 *
 * Class Hydrator
 * @package Virton\Database
 */
class Hydrator
{
    /**
     * Allows to manipulate entities.
     * @param array $array
     * @param string|object $object
     * @return mixed
     */
    public static function hydrate(array $array, $object)
    {
        if (is_string($object)) {
            $instance = new $object();
        } else {
            $instance = $object;
        }
        foreach ($array as $key => $value) {
            $method = self::getSetter($key);
            if (method_exists($instance, $method)) {
                $instance->$method($value);
            } else {
                $property = lcfirst(self::getProperty($key));
                $instance->$property = $value;
            }
        }
        return $instance;
    }

    /**
     * Gets the proprety of an entity.
     * @param string $fieldName
     * @return string
     */
    private static function getProperty(string $fieldName): string
    {
        return join('', array_map('ucfirst', explode('_', $fieldName)));
    }

    /**
     * Catches the entity setter.
     * @param string $fieldName
     * @return string
     */
    private static function getSetter(string $fieldName): string
    {
        return 'set' . self::getProperty($fieldName);
    }
}
