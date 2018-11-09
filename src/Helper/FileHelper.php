<?php
namespace Virton\Helper;

class FileHelper
{
    /**
     * Get the DOM of a file
     *
     * @param string $file
     * @return string|null
     */
	public static function getContent(string $file): ?string
	{
		if (file_exists($file)) {
			return file_get_contents($file);
		}
		return null;
	}

	/**
	 * @param string|string[] $extension
	 * @param string $filePath
	 * @return bool
	 * @throws \Exception
	 */
	public static function isExtension($extension, string $filePath): bool
	{
		$pathParts = explode('.', $filePath);
		$fileExtension = end($pathParts);
		return ($fileExtension === $extension) ? true : false;
	}

	public static function parseXMLFile(string $filePath)
	{
		return ArrayHelper::parseXML(self::getContent($filePath));
	}

	public static function parseYAMLFile(string $filePath)
	{
		return ArrayHelper::parseYAML(self::getContent($filePath));
	}

	public static function parseJSONFile(string $filePath)
	{
		return ArrayHelper::parseJSON(self::getContent($filePath));
	}

	public static function parsePHPFile(string $filePath)
	{
		return require $filePath;
	}

	public static function parseINIFile($filePath)
	{
		return parse_ini_file($filePath);
	}
}
