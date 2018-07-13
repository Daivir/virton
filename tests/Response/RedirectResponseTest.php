<?php
namespace Tests\Response;

use Virton\Response\RedirectResponse;
use PHPUnit\Framework\TestCase;

class RedirectResponseTest extends TestCase
{
    public function testStatus()
    {
        $response = new RedirectResponse('/test');
        $this->assertEquals(301, $response->getStatusCode());
    }

    public function testHeader()
    {
        $response = new RedirectResponse('/test');
        $this->assertEquals(['/test'], $response->getHeader('Location'));
    }
}
