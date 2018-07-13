<?php
namespace Tests\Helpers;

use Virton\Helper\ArrayHelper;
use PHPUnit\Framework\TestCase;

class ArrayHelperTest extends TestCase
{
	public $xmlString = <<<XML
<?xml version='1.0' standalone='yes' ?>
<keys>
  <key0>Value0</key0>
  <key1>
    <key10>Value10</key10>
    <key11>Value11</key11>
    <key12>Value12</key12>
  </key1>
  <key2>Value2</key2>
</keys>
XML;

    public function testXMLparser()
    {
    	$arrayFromXML = ArrayHelper::parseXML($this->xmlString);
    	$expectedArray = [
            'key0' => 'Value0',
		    'key1' => [
		        'key10' => 'Value10',
			    'key11' => 'Value11',
			    'key12' => 'Value12'
		    ],
		    'key2' => 'Value2'
	    ];
		$this->assertTrue(is_array($arrayFromXML));
		$this->assertEquals($expectedArray, $arrayFromXML);
    }
}
