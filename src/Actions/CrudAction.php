<?php
namespace Virton\Actions;

use Virton\Database\Hydrator;
use Virton\Database\Table;
use Virton\Renderer\RendererInterface;
use Virton\Router;
use Virton\Session\FlashHandler;
use Virton\Validator;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Handle the CRUD Action
 * class CrudAction
 * @package Virton\Actions
 */
class CrudAction
{
    /**
     * @var Table table
     */
    protected $table;

    /**
     * @var FlashHandler
     */
    private $flash;

    /**
     * @var RendererInterface
     */
    private $renderer;

    /**
     * @var Router
     */
    private $router;

    /**
     * The path of the views.
     * @var string
     */
    protected $viewPath;

    /**
     * @var string
     */
    protected $routePrefix;

    /**
     * @var string
     */
    protected $messages = [
        'create' => 'This element has been successfully created',
        'edit' => 'This element has been successfully updated'
    ];

    /**
     * @var array
     */
    protected $acceptedParams = [];

    use RouterAwareAction;

    public function __construct(
        RendererInterface $renderer,
        Router $router,
        Table $table,
        FlashHandler $flash
    ) {
        $this->router = $router;
        $this->table = $table;
        $this->renderer = $renderer;
        $this->flash = $flash;
    }

    public function __invoke(ServerRequestInterface $request)
    {
        $this->renderer->addGlobal('viewPath', $this->viewPath);
        $this->renderer->addGlobal('routePrefix', $this->routePrefix);
        if ($request->getMethod() === 'DELETE') {
            return $this->delete($request);
        }
        if (substr((string) $request->getUri(), -3) === 'new') {
            return $this->create($request);
        }
        if ($request->getAttribute('id')) {
            return $this->edit($request);
        }
        return $this->index($request);
    }

    /**
     * Displays a list of the elements.
     * @param ServerRequestInterface $request
     * @return string
     */
    public function index(ServerRequestInterface $request): string
    {
        $params = $request->getQueryParams();
        $items = $this->table->findAll()->paginate(20, $params['p'] ?? 1);
        return $this->renderer->render($this->viewPath . '/index', compact('items'));
    }

    /**
     * Edits an element.
     * @param ServerRequestInterface $request
     * @return ResponseInterface|string
     * @throws \Framework\Database\NoRecordException
     */
    public function edit(ServerRequestInterface $request)
    {
        $id = (int)$request->getAttribute('id');
        $item = $this->table->find($id);

        if ($request->getMethod() === 'POST') {
            $validator = $this->getValidator($request);
            if ($validator->isValid()) {
                $this->table->update($id, $this->prePersist($request, $item));
                $this->postPersist($request, $item);
                $this->flash->success($this->messages['edit']);
                return $this->redirect($this->routePrefix . '.index');
            }
            $item = Hydrator::hydrate($request->getParsedBody(), $item);
            $errors = $validator->getErrors();
        }

        return $this->renderer->render(
            $this->viewPath . '/edit',
            $this->formParams(compact('item', 'errors'))
        );
    }

    /**
     * Creates an element.
     * @param ServerRequestInterface $request
     * @return ResponseInterface|string
     */
    public function create(ServerRequestInterface $request)
    {
        $item = $this->getNewEntity();
        if ($request->getMethod() === 'POST') {
            $validator = $this->getValidator($request);
            if ($validator->isValid()) {
                $this->table->insert($this->prePersist($request, $item));
                $this->postPersist($request, $item);
                $this->flash->success($this->messages['create']);
                return $this->redirect($this->routePrefix . '.index');
            }
            $item = Hydrator::hydrate($request->getParsedBody(), $item);
            $errors = $validator->getErrors();
        }

        return $this->renderer->render(
            $this->viewPath . '/create',
            $this->formParams(compact('item', 'errors'))
        );
    }

    /**
     * Deletes an element.
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    protected function delete(ServerRequestInterface $request): ResponseInterface
    {
        $this->table->delete($request->getAttribute('id'));
        return $this->redirect($this->routePrefix . '.index');
    }

    /**
     * Pre-process the persistence.
     * @param ServerRequestInterface $request
     * @return array
     */
    protected function prePersist(ServerRequestInterface $request, $item): array
    {
        return array_filter(array_merge($request->getParsedBody(), $request->getUploadedFiles()), function ($key) {
            return in_array($key, $this->acceptedParams);
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * Post-process the persistence.
     * @param ServerRequestInterface $request
     * @param $item
     */
    protected function postPersist(ServerRequestInterface $request, $item): void
    {
    }

    protected function getValidator(ServerRequestInterface $request)
    {
        return new Validator(array_merge($request->getParsedBody(), $request->getUploadedFiles()));
    }

    /**
     * Generates a new entity for the create action.
     * @return mixed
     */
    protected function getNewEntity()
    {
        $entity = $this->table->getEntity();
        return new $entity();
    }

    /**
     * Allows to process parameters to send to the view
     *
     * @param array $params
     * @return array
     */
    protected function formParams(array $params): array
    {
        return $params;
    }
}
