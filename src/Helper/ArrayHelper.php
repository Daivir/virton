<?php
namespace Virton\Helper;

class ArrayHelper
{
    private static $instance;

    public static function getInstance(): self
    {
        if (isset(self::$instance)) {
            return self::$instance;
        }
        return self::$instance = new self;
    }

    /**
     * Check if the keys exist in an array.
     * @param array $keys
     * @param array $array
     * @return array|true
     */
    public static function keysExists(array $keys, array $array)
    {
        return array_diff_key($array, $keys) ?: true;
    }

    public static function get(string $index, array $array)
    {
        return self::has($index, $array) ? $array[$index] : null;
    }

    public static function has(string $index, array $array): bool
    {
        return array_key_exists($index, $array) ? true : false;
    }

    public static function first(array $array)
    {
        return array_shift($array);
    }

    public static function next(array $array): array
    {
        array_shift($array);
        return $array;
    }

    public static function find(string $index, array $array)
    {
        if (array_key_exists($index, $array)) {
            return $array[$index];
        }
        if ($index === '*') {
        	return $array;
        }
        $parts = explode('.', $index);
        $firstIndex = self::first($parts);
        $nextIndex = implode('.', self::next($parts));
        if ($firstIndex === '*') {
            return array_column($array, $nextIndex);
        }
        if (is_string(self::get($firstIndex, $array))) {
            return null;
        }
        if (isset($array[$firstIndex])) {
            return self::find($nextIndex, self::get($firstIndex, $array));
        }
        return null;
    }

	/**
	 * Flatten a nested array at the same level.
	 * @param array $array
	 * @param string $join
	 * @param null|string $prepend
	 * @return array
	 */
    public static function flatten(array $array, string $join = '.', $prepend = null): array
    {
	    if (!isset($result)) {
		    $result = [];
	    }
	    foreach ($array as $key => $value) {
		    if (is_array($value) && !self::isSequential($value)) {
			    $result = $result + self::flatten($value, $join, $prepend . $key . $join);
		    } else {
			    $result[$prepend . $key] = $value;
		    }
	    }
	    return $result;
    }

	/**
	 * Group the arrays according to the values ​​of the specified key.
	 * @param $key
	 * @param array $array
	 * @return array
	 */
    public static function groupBy($key, array $array)
    {
        $result = [];
        foreach ($array as $data) {
            $id = $data[$key];
            if (isset($result[$id])) {
                $result[$id][] = $data;
            } else {
                $result[$id] = [$data];
            }
        }
        return $result;
    }

	/**
	 * Parse XML into a nested array
	 * @param $xmlString
	 * @return array
	 */
    public static function parseXML(string $xmlString): array
    {
	    $parsedXML = new \SimpleXMLElement($xmlString);
	    return json_decode(json_encode($parsedXML), true);
    }

	/**
	 * Parse YAML into a nested array
	 * @param string $yamlString
	 * @return array
	 */
	public static function parseYAML(string $yamlString): array
	{
		return yaml_parse($yamlString);
	}

	/**
	 * Parse JSON into a nested array
	 * @param string $jsonString
	 * @return array
	 */
	public static function parseJSON(string $jsonString): array
	{
		$jsonIterator = new \RecursiveIteratorIterator(
			new \RecursiveArrayIterator(json_decode($jsonString, TRUE)),
			\RecursiveIteratorIterator::SELF_FIRST);

		$result = [];
		foreach ($jsonIterator as $k => $v) {
			$result[] = [$k => $v];
		}

		return $result;
	}

	/**
	 * Check if the keys of the array is a sequence of numbers
	 * ["a", "b", "c"] => true
	 * [1 => "a", 0 => "b", 2 => "c"] => false
	 *
	 * @param array $array
	 *
	 * @return bool
	 */
    public static function isSequential(array $array): bool
    {
        if (empty($array)) {
            return true;
        }
        return array_keys($array) === range(0, (count($array) -1));
    }
}
