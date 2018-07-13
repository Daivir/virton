<?php
namespace Tests\Modules;

use Virton\Router;

class ModuleStringTest
{
	public function __construct(Router $router)
	{
		$router->get('/moduletest', function () {
			return "moduletest";
		}, 'moduletest');
	}
}
