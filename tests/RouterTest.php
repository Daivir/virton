<?php
namespace Tests;

use Virton\Router;
use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Virton\Router\Route;

class RouterTest extends TestCase
{
	/**
	 * @var Router
	 */
	private $router;

	public function setUp()
	{
		$this->router = new Router;
	}

	public function testAnyMethod()
    {
        $request = new ServerRequest('GET', '/get');
        $this->router->any('/get', function () {}, 'get');
        $route = $this->router->match($request);
        $this->assertEquals("get", $route->getName());

        $requestPost = new ServerRequest('POST', '/post');
        $this->router->any('/post', function () {}, 'post');
        $routePost = $this->router->match($requestPost);
        $this->assertEquals("post", $routePost->getName());
    }

	public function testGetMethod()
	{
		$request = new ServerRequest('GET', '/test');

		$this->router->get('/test', function () {
			return "TEST";
		}, 'test');

		$route = $this->router->match($request);

		$this->assertEquals("test", $route->getName());
		$this->assertEquals("TEST", call_user_func_array($route->getCallback(), [$request]));
	}

	public function testGetMethodIfUriDoesNotExists()
	{
		$request = new ServerRequest('GET', '/test');

		$this->router->get('/no-test', function () {
			return "TEST";
		}, 'test');

		$route = $this->router->match($request);

		$this->assertEquals(null, $route);
	}

	public function testGetMethodWithParams()
	{
		$request = new ServerRequest('GET', '/test/a-slug-1');

		$this->router->get('/test', function () {
			return "TESTS";
		}, 'tests');
		$this->router->get('/test/{slug:[a-z\-]+}-{id:[0-9]+}', function () {
			return "TEST";
		}, 'test.show');

		$route = $this->router->match($request);

		$this->assertEquals('test.show', $route->getName());
		$this->assertEquals("TEST", call_user_func_array($route->getCallback(), [$request]));
		$this->assertEquals(['slug' => "a-slug", 'id' => "1"], $route->getParams());
	}

	public function testGetMethodInvalidUri()
	{
		$request = new ServerRequest('GET', '/blog/a_slug-1');

		$this->router->get('/test', function () {
			return "TESTS";
		}, 'tests');
		$this->router->get('/test/{slug:[a-z\-]+}-{id:[0-9]+}', function () {
			return "TEST";
		}, 'test.show');

		$route = $this->router->match($request);

		$this->assertEquals(null, $route);
	}

    public function testPostDeleteMethod()
    {
        $fake = function () {
            return 'hello';
        };
        $this->router->get('/blog', $fake, 'blog');
        $this->router->post('/blog', $fake, 'blog.post');
        $this->router->delete('/blog', $fake, 'blog.delete');
        $this->assertEquals('blog', $this->router->match(new ServerRequest('GET', '/blog'))->getName());
        $this->assertEquals('blog.post', $this->router->match(new ServerRequest('POST', '/blog'))->getName());
        $this->assertEquals('blog.delete', $this->router->match(new ServerRequest('DELETE', '/blog'))->getName());
    }

    public function testCrudMethod()
    {
        $this->router->crud('/blog', function () {
        }, 'blog');
        $this->assertEquals('blog.index', $this->router->match(new ServerRequest('GET', '/blog'))->getName());
        $this->assertEquals('blog.create', $this->router->match(new ServerRequest('GET', '/blog/new'))->getName());
        $this->assertInstanceOf(Route::class, $this->router->match(new ServerRequest('POST', '/blog/new')));
        $this->assertEquals('blog.edit', $this->router->match(new ServerRequest('GET', '/blog/1'))->getName());
        $this->assertInstanceOf(Route::class, $this->router->match(new ServerRequest('POST', '/blog/1')));
        $this->assertInstanceOf(Route::class, $this->router->match(new ServerRequest('DELETE', '/blog/1')));
    }

	public function testGenerateUri()
	{
		$this->router->get('/test', function () {
			return "TESTS";
		}, 'tests');
		$this->router->get('/test/{slug:[a-z\-]+}-{id:[0-9]+}', function () {
			return "TEST";
		}, 'test.show');

		$uri = $this->router->generateUri('test.show', ['slug' => "a-slug", 'id' => "1"]);

		$this->assertEquals('/test/a-slug-1', $uri);
	}

	public function testGenerateUriWithQueryParams()
	{
		$this->router->get('/test', function () {
			return "TESTS";
		}, 'tests');
		$this->router->get('/test/{slug:[a-z\-]+}-{id:[0-9]+}', function () {
			return "TEST";
		}, 'test.show');

		$uri = $this->router->generateUri(
			'test.show',
			['slug' => "a-slug", 'id' => "1"],
			['p' => 2]
		);

		$this->assertEquals('/test/a-slug-1?p=2', $uri);
	}
}
