<?php
namespace ValuePad\Api\Support\Converter\Extractor;

use Ascope\Libraries\Converter\Extractor\Root;
use Illuminate\Container\Container;
use ValuePad\Api\Support\Converter\Extractor\Filters\NullFilter;

class FilterWithinContext
{
	/**
	 * @var Container
	 */
	private $container;

	/**
	 * @var array
	 */
	private $filters = [];

	/**
	 * @param Container $container
	 */
	public function __construct(Container $container)
	{
		$this->container = $container;
		$this->filters = $container->make('config')->get('transformer.filters', []);
	}

	/**
	 * @param string $key
	 * @param object $object
	 * @param Root $root
	 * @return bool
	 */
	public function __invoke($key, $object, Root $root = null)
	{
		return $this->resolveFilter($object)->isAllowed($key, $object, $root);
	}

	/**
	 * @param $object
	 * @return FilterInterface
	 */
	private function resolveFilter($object)
	{
		$newClass = get_class($object);

		if (!isset($this->filters[$newClass])){

			foreach ($this->filters as $class => $filter){
				if ($object instanceof $class){
					$this->filters[$newClass] = $filter;
					break ;
				}
			}

			if (!isset($this->filters[$newClass])){
				$this->filters[$newClass] =  new NullFilter();
			}
		}

		if (is_string($this->filters[$newClass])){
			$this->filters[$newClass] = new $this->filters[$newClass]($this->container);
		}

		return $this->filters[$newClass];
	}
}
