<?php
namespace Virton;

use Virton\Router\Route;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Expressive\Router\FastRouteRouter;
use Zend\Expressive\Router\Route as ZendRoute;

/**
 * Registering and matching routes.
 *
 * Class Router
 * @package Virton
 */
class Router
{
	/**
	 * @var FastRouteRouter
	 */
	private $router;

    /**
     * Router constructor.
     * @param string|null $cache
     */
	public function __construct(?string $cache = null)
	{
		$this->router = new FastRouteRouter(null, null, [
			FastRouteRouter::CONFIG_CACHE_ENABLED => !is_null($cache),
			FastRouteRouter::CONFIG_CACHE_FILE => $cache
		]);
	}

    /**
     * Adds a route with any methods to the router system.
     * @param string $path
     * @param $callback
     * @param null|string $name
     */
	public function any(string $path, $callback, ?string $name = null): void
    {
        $this->router->addRoute(new ZendRoute($path, $callback, ['GET', 'POST', 'PUT', 'DELETE'], $name));
    }

	/**
	 * Adds a route with GET method to the router system.
	 * @param string $path
	 * @param string|callable $callback
	 * @param string $name
     * @return void
	 */
	public function get(string $path, $callback, ?string $name = null): void
	{
		$this->router->addRoute(new ZendRoute($path, $callback, ['GET'], $name));
	}

    /**
     * Adds a route with POST method to the router system.
     * @param string $path
     * @param $callback
     * @param string|null $name
     * @return void
     */
	public function post(string $path, $callback, ?string $name = null): void
	{
		$this->router->addRoute(new ZendRoute($path, $callback, ['POST'], $name));
	}

    /**
     * Adds a route with DELETE method to the router system.
     * @param string $path
     * @param $callback
     * @param string|null $name
     */
	public function delete(string $path, $callback, ?string $name = null): void
	{
		$this->router->addRoute(new ZendRoute($path, $callback, ['DELETE'], $name));
	}

	public function options(string $path, $callback, ?string $name = null): void
	{
		$this->router->addRoute(new ZendRoute($path, $callback, ['OPTIONS'], $name));
	}

    /**
     * Generates the routes of the 'Create Read Update Delete' action.
     * @param string $prefixPath
     * @param $callback
     * @param string $prefixName
     */
	public function crud(string $prefixPath, $callback, string $prefixName): void
	{
		$this->get("$prefixPath", $callback, "$prefixName.index");
		$this->get("$prefixPath/new", $callback, "$prefixName.create");
		$this->post("$prefixPath/new", $callback);
		$this->get("$prefixPath/{id:\d+}", $callback, "$prefixName.edit");
		$this->post("$prefixPath/{id:\d+}", $callback);
		$this->delete("$prefixPath/{id:\d+}", $callback, "$prefixName.delete");
	}

	/**
	 * Match existing routes with the requested URL.
	 * @param ServerRequestInterface $request
	 * @return Route|null
	 */
	public function match(ServerRequestInterface $request): ?Route
	{
		$result = $this->router->match($request); // instance of \Zend\Expressive\Router\RouteResult
		if ($result->isSuccess()) {
			return new Route(
				$result->getMatchedRouteName(),
				$result->getMatchedMiddleware(),
				$result->getMatchedParams()
			);
		}
		return null;
	}

	/**
	 * Generates URI from parameters.
	 * @param string $name
	 * @param string[] $params
	 * @param array $queryParams
	 * @return string|null
	 */
	public function generateUri(string $name, array $params = [], array $queryParams = []): ?string
	{
	    $uri = $this->router->generateUri($name, $params);

		if (!empty($queryParams)) {
			return $uri . '?' . http_build_query($queryParams);
		}
		return $uri;
	}
}
