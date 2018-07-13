<?php
namespace Tests;

use Virton\Renderer\PHPRenderer;
use PHPUnit\Framework\TestCase;

class PHPRendererTest extends TestCase
{
	private $renderer;

	public function setUp()
	{
		$this->renderer = new PHPRenderer(dirname(__DIR__) . '/views');
	}

	public function testRenderRightPath()
	{
		$this->renderer->addPath('blog', dirname(__DIR__) . '/views');
		$content = $this->renderer->render('@blog/test');
		$this->assertEquals('Hello World!', $content);
	}

	public function testRenderTemplateDefaultPath()
	{
		$content = $this->renderer->render('test');
		$this->assertEquals('Hello World!', $content);
	}

	public function testRenderWithParams()
	{
		$content = $this->renderer->render('test-params', ['name' => 'Marc']);
		$this->assertEquals('Hello Marc!', $content);
	}

	public function testGlobalParams()
	{
		$this->renderer->addGlobal('name', "Marc");
		$content = $this->renderer->render('test-params', ['name' => 'Marc']);
		$this->assertEquals('Hello Marc!', $content);
	}
}
