<?php
namespace Tests\Twig;

use PHPUnit\Framework\TestCase;
use Virton\Twig\TextExtension;

class TextExtensionTest extends TestCase
{
    /**
     * @var TextExtension
     */
    private $textExtension;

    public function setUp()
    {
        $this->textExtension = new TextExtension();
    }

    public function testExcerptWithShortText()
    {
        $text = "Hey!";
        $this->assertEquals($text, $this->textExtension->excerpt($text, 10));
    }

    public function testExcerptWithLongText()
    {
        $text = "Hello World! How are you?";
        $this->assertEquals("Hello...", $this->textExtension->excerpt($text, 7));
        $this->assertEquals("Hello World!...", $this->textExtension->excerpt($text, 14));
    }
}
