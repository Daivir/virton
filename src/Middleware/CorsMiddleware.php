<?php
namespace Virton\Middleware;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class CorsMiddleware implements MiddlewareInterface
{
	private $options = [
		"origin" => "*",
		"headers" => ["X-Requested-With", "Content-Type", "Accept", "Origin", "Authorization"],
		"methods" => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS']
	];

	public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
	{
		$response = $handler->handle($request);
		return $response
			->withHeader('Access-Control-Allow-Origin', $this->options["origin"])
			->withHeader('Access-Control-Allow-Headers', join(", ", $this->options["headers"]))
			->withHeader('Access-Control-Allow-Methods', join(", ", $this->options["methods"]));
	}
}
