<?php
namespace Tests\Helpers;

use Virton\Helper\StringHelper;
use PHPUnit\Framework\TestCase;

class StringHelperTest extends TestCase
{
    public function testCamelize()
    {
        $this->assertEquals('testCase', StringHelper::camelize('test_case'));
        $this->assertEquals(
            'testCaseSecond',
            StringHelper::camelize('test_case_second')
        );
    }
}
