<?php
namespace ValuePad\Api\Support;

use Ascope\Libraries\Kangaroo\Pagination\AdapterInterface;
use Ascope\Libraries\Kangaroo\Pagination\Paginator;
use Ascope\Libraries\Permissions\PermissionsRequirableInterface;
use Ascope\Libraries\Verifier\ActionVerifiableInterface;
use Illuminate\Container\Container;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Ascope\Libraries\Transformer\AbstractTransformer;
use RuntimeException;
use Ascope\Libraries\Kangaroo\Resource\Manager as ResourceManager;

class BaseController extends Controller implements PermissionsRequirableInterface, ActionVerifiableInterface
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * @var ResourceManager
     */
    protected $resource;

    /**
     * BaseController constructor.
     *
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->resource = $this->container->make(ResourceManager::class);

        if (method_exists($this, 'initialize')) {
            $this->container->call([$this, 'initialize']);
        }
    }

    /**
     * @param string $class
     * @return AbstractTransformer
     * @throws RuntimeException
     */
    protected function transformer($class = DefaultTransformer::class)
    {
        /**
         * @var TransformerFactory $factory
         */
        $factory = $this->container->make(TransformerFactory::class);

        /**
         * @var Request $request
         */
        $request = $this->container->make(Request::class);
        $input = $request->header('Include');

        $fields = $input ? array_map('trim', explode(',', $input)) : [];

        $config = $this->container->make('config')->get('transformer');

        return $factory->create($class, $config)->setIncludes($fields);
    }

	/**
	 * @param AdapterInterface $adapter
	 * @return Paginator
	 */
	protected function paginator(AdapterInterface $adapter)
	{
        Paginator::setRequest($this->container->make(Request::class));

        return new Paginator($adapter);
	}
}
