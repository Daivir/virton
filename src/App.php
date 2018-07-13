<?php
namespace Virton;

use DI\ContainerBuilder;
use Doctrine\Common\Cache\FilesystemCache;
use Virton\{
	Helper\ArrayHelper,
	Helper\FileHelper,
	Middleware\CombinedMiddleware,
	Middleware\RouterPrefixMiddleware
};
use Psr\Container\ContainerInterface;
use Psr\Http\{
	Message\ResponseInterface,
	Message\ServerRequestInterface,
	Server\MiddlewareInterface,
	Server\RequestHandlerInterface
};

class App implements RequestHandlerInterface
{
	/**
	 * List of modules.
	 * @var string[]
	 */
	private $modules = [];

	/**
	 * Paths of the configurations files.
	 * @var array
	 */
	private $definitions;

	/**
	 * Container.
	 * @var ContainerInterface
	 */
	private $container;

	/**
	 * @var string[]
	 */
	private $middlewares = [];

	/**
	 * App constructor.
	 * @param string|string[]|array $definitions
	 */
	public function __construct($definitions = [])
	{
		if (is_string($definitions) || !ArrayHelper::isSequential($definitions)) {
            $definitions = [$definitions];
        }
        $this->definitions = $definitions;
	}

	/**
	 * Adds a module to the application.
	 * @param string $module
	 * @return self
	 */
	public function addModule(string $module): self
	{
		$this->modules[] = $module;
		return $this;
	}

	/**
	 * Adds a middleware.
	 * @param string|callable|MiddlewareInterface $routePrefix
	 * @param null|string|callable|MiddlewareInterface $middleware
	 * @return App
	 * @throws \Exception
	 */
	public function pipe($routePrefix, $middleware = null): self
	{
		if ($middleware === null) {
			$this->middlewares[] = $routePrefix;
		} else {
			$this->middlewares[] = new RouterPrefixMiddleware($this->getContainer(), $routePrefix, $middleware);
		}
		return $this;
	}

    /**
     * Middlewares handler.
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws \Exception
     */
	public function handle(ServerRequestInterface $request): ResponseInterface
	{
        $middleware = new CombinedMiddleware($this->getContainer(), $this->middlewares);
		return $middleware->process($request, $this);
	}

	/**
	 * Loads modules then run the application.
	 *
	 * @param ServerRequestInterface $request
	 *
	 * @return ResponseInterface
	 *
	 * @throws \Exception
	 * @throws \Psr\Container\ContainerExceptionInterface
	 * @throws \Psr\Container\NotFoundExceptionInterface
	 */
	public function run(ServerRequestInterface $request): ResponseInterface
	{
		foreach ($this->modules as $module) {
			$this->getContainer()->get($module);
		}
		return $this->handle($request);
	}

	/**
	 * @return ContainerInterface
	 * @throws \Exception
	 */
	public function getContainer(): ContainerInterface
	{
		if (is_null($this->container)) {
			$builder = new ContainerBuilder;
			// TODO: refactor env management
			if (isset($_ENV['ENV'])) {
				$env = $_ENV['ENV'] ?: 'production';
			} else {
				$env = $_ENV['ENV'] = 'dev';
			}
			if ($env == 'production') {
				$builder->setDefinitionCache(new FilesystemCache('tmp/di'));
				$builder->writeProxiesToFile(true, 'tmp/proxies');
			}

            foreach ($this->definitions as $definition) {
				if (is_array($definition)) {
					$builder->addDefinitions(ArrayHelper::flatten($definition));
                } else {
		            if (FileHelper::isExtension('php', $definition)) {
			            $builder->addDefinitions(ArrayHelper::flatten(FileHelper::parsePHPFile($definition)));
		            }
		            if (FileHelper::isExtension('yaml', $definition)) {
			            $builder->addDefinitions(ArrayHelper::flatten(FileHelper::parseYAMLFile($definition)));
		            }
		            if (FileHelper::isExtension('json', $definition)) {
			            $builder->addDefinitions(ArrayHelper::flatten(FileHelper::parseJSONFile($definition)));
		            }
	            }
            }

            foreach ($this->modules as $module) {
                /** @var \Virton\Module $module */
                $definitions = $module::DEFINITIONS;
				if ($definitions) {
					$builder->addDefinitions($definitions);
				}
			}
            $builder->addDefinitions([App::class => $this]);
            $this->container = $builder->build();
		}
		return $this->container;
	}

    /**
     * @return array
     */
	public function getModules(): array
	{
		return $this->modules;
	}
}
