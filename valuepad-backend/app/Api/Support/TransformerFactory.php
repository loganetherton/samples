<?php
namespace ValuePad\Api\Support;

use Ascope\Libraries\Transformer\AbstractTransformer;
use Illuminate\Contracts\Container\Container;
use RuntimeException;

class TransformerFactory
{
	/**
	 * @var Container
	 */
	private $container;

	/**
	 * @param Container $container
	 */
	public function __construct(Container $container)
	{
		$this->container = $container;
	}

	/**
	 * @param string $class
     * @param array $config
	 * @return AbstractTransformer
	 */
	public function create($class, array $config)
	{
		$transformer = $this->container->make($class);

		if (!$transformer instanceof AbstractTransformer) {
			throw new RuntimeException('The transformer should be instance of AbstractTransformer');
		}

		$calculatedProperties = [];

		foreach (array_take($config, 'calculatedProperties', []) as $target => $options) {
			$calculatedProperties[$target] = array_map(function($callback){
				return  $this->container->make($callback);
			}, $options);
		}

		$specifications = [];

		foreach (array_take($config, 'specifications', []) as $target => $options) {
			$specifications[$target] = array_map(function($callback){
				return $this->container->make($callback);
			}, $options);
		}

		$transformer
			->setCalculatedProperties($calculatedProperties)
			->setSpecifications($specifications)
			->setModifiers(array_take($config, 'modifiers', []))
			->setDefaults(array_get($config, 'include.default', []))
			->setIgnores(array_get($config, 'include.ignore'));

		if ($filter = array_take($config, 'filter')){
			$transformer->setFilter($this->container->make($filter));
		}

		return $transformer;
	}
}
