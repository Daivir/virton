<?php
namespace Tests\Twig;

use PHPUnit\Framework\TestCase;
use Virton\Twig\TimeExtension;

class TimeExtensionTest extends TestCase
{
    private $timeExtension;

    public function setUp()
    {
        $this->timeExtension = new TimeExtension();
    }

    public function testDateFormat()
    {
        $date = new \DateTime();
        $dateFormatDisplay = $date->format('d/m/Y H:i');
        $dateFormatParam = $date->format(\DateTime::ISO8601);
        $result = "<span class=\"timeago\" datetime=\"$dateFormatParam\">$dateFormatDisplay</span>";
        $this->assertEquals($result, $this->timeExtension->ago($date));
    }
}
