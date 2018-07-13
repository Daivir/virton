<?php
namespace Virton\DOMParser;

class XMLParser
{
    /**
     * @var self
     */
    private static $instance;

    public static function getInstance()
    {
        if (isset(self::$instance)) {
            return self::$instance;
        }
        return self::$instance = new XMLParser();
    }

    public static function factoryPhpUnitXml(string $path)
    {
        if (file_exists($path)) {
            return self::$instance->parsePhpUnitXml(simplexml_load_file($path));
        }
        return "Failed to open \"$path\"";
    }

    public function parsePhpUnitXml(\SimpleXMLElement $file)
    {
        $root = $file->testsuite;

        $globals = $root->attributes();

        $errors = [];
        $failures = [];

        foreach ($root->testsuite as $key => $value) {
            foreach ($value->testcase as $k => $v) {
                if ($v->error) {
                    $name = (string)$value->attributes()->name;
                    $errors[$name][(string)$v->attributes()->name] = $v->attributes();
                }
                if ($v->failure) {
                    $name = (string)$value->attributes()->name;
                    $failures[$name][(string)$v->attributes()->name] = $v->attributes();
                }
            }
        }
        return [
            'globals' => $globals,
            'errors' => $errors,
            'failures' => $failures
        ];
    }
}
