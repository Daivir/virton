<?php
namespace Tests\Modules;

class ModuleStringTest
{
	public function __construct(\Framework\Router $router)
	{
		$router->get('/moduletest', function () {
			return "moduletest";
		}, 'moduletest');
	}
}
