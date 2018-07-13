<?php
namespace Tests\Session;

use PHPUnit\Framework\TestCase;
use Virton\Session\ArraySession;
use Virton\Session\FlashHandler;

class FlashHandlerTest extends TestCase
{
    private $session;
    private $flash;

    public function setUp()
    {
        $this->session = new ArraySession;
        $this->flash = new FlashHandler($this->session);
    }

    public function testFlash()
    {
        $flash = new FlashHandler($this->session);
        $flash->default('Default!');
        $this->assertEquals('Default!', $flash->get('default'));

        $flash = new FlashHandler($this->session);
        $flash->danger('Danger!');
        $this->assertEquals('Danger!', $flash->get('danger'));

        $flash = new FlashHandler($this->session);
        $flash->success('Success!');
        $this->assertEquals('Success!', $flash->get('success'));

        $flash = new FlashHandler($this->session);
        $flash->warning('Warning!');
        $this->assertEquals('Warning!', $flash->get('warning'));

        $flash = new FlashHandler($this->session);
        $flash->info('Info!');
        $this->assertEquals('Info!', $flash->get('info'));
    }

    public function testWithdrawFlash()
    {
        $this->flash->success("Success!");
        $this->assertEquals("Success!", $this->flash->get('success'));
        $this->assertNull($this->session->get('flash'));
        $this->assertEquals("Success!", $this->flash->get('success'));
    }
}
