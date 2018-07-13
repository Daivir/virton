<?php

namespace Tests\Twig;

use PHPUnit\Framework\TestCase;
use Virton\Twig\FormExtension;

class FormExtensionTest extends TestCase
{
	/**
	 * @var FormExtension
	 */
	private $formExtension;

	public function setUp()
	{
		$this->formExtension = new FormExtension();
	}

	public function testFieldText()
	{
		$html = $this->formExtension->field([], 'name', 'test', 'Title');
		$this->assertSimilarString("
            <div class=\"form-group\">
                <label for=\"name\">Title</label>
                <input type=\"text\" class=\"form-control\" name=\"name\" id=\"name\" value=\"test\" />
            </div>
        ", $html);
	}

	public function testFieldTextArea()
	{
		$html = $this->formExtension->field([], 'name', 'test', 'Title', ['type' => 'textarea']);
		$this->assertSimilarString("
            <div class=\"form-group\">
                <label for=\"name\">Title</label>
                <textarea class=\"form-control\" name=\"name\" id=\"name\" rows=\"12\">test</textarea>
            </div>
        ", $html);
	}

	public function testFieldWithErrors()
	{
		$context = ['errors' => ['name' => 'error message test']];
		$html = $this->formExtension->field($context, 'name', 'test', 'Title');
		$this->assertSimilarString("
            <div class=\"form-group\">
                <label for=\"name\">Title</label>
                <input type=\"text\" class=\"form-control is-invalid\" name=\"name\" id=\"name\" value=\"test\" />
                <div class=\"invalid-feedback\">error message test</div>
            </div>", $html);
	}

	public function testFieldWithClass()
	{
		$html = $this->formExtension->field([], 'name', 'test', 'Title', ['class' => 'test']);
		$this->assertSimilarString("
            <div class=\"form-group\">
                <label for=\"name\">Title</label>
                <input type=\"text\" class=\"form-control test\" name=\"name\" id=\"name\" value=\"test\" />
            </div>
        ", $html);
	}

	public function testSelect()
	{
		$options = [1 => 'Test', 2 => 'Test2'];
		$html = $this->formExtension->field([], 'name', 2, 'Title', ['options' => $options]);
		$this->assertSimilarString("
            <div class=\"form-group\">
                <label for=\"name\">Title</label>
                <select class=\"form-control\" name=\"name\" id=\"name\">
                    <option value=\"1\">Test</option>
                    <option value=\"2\" selected>Test2</option>
                </select>
            </div>
        ", $html);
	}

	private function assertSimilarString(string $expected, string $actual)
	{
		$this->assertEquals($this->strtrim($expected), $this->strtrim($actual));
	}

	private function strtrim(string $string)
	{
		$lines = explode("\n", $string);
		$lines = array_map('trim', $lines);
		return implode('', $lines);
	}
}
