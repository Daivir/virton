<?php
namespace Tests\Modules;

use Virton\Router;

class ModuleExceptionTest
{
	public function __construct(Router $router)
	{
		$router->get('/moduletest', function () {
			return new \stdClass();
		}, 'moduletest');
	}
}
