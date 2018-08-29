<?php
namespace ValuePad\Api\Support;

use  Ascope\Libraries\Transformer\SharedModifiers;
use Illuminate\Contracts\Container\Container as ContainerInterface;

class TransformerModifiers extends SharedModifiers
{
	/**
	 * @param ContainerInterface $container
	 */
	public function __construct(ContainerInterface $container)
	{
		parent::__construct([
			'stringable' => $container->make('config')->get('transformer.stringable', [])
		]);
	}

    /**
     * @param string $value
     * @return string
     */
	public function purifier($value)
	{
		return clean($value);
	}

    /**
     * @param string $value
     * @return string
     */
	public function mask($value)
    {
        if ($value === null){
            return $value;
        }

        return substr($value, -4, 4);
    }
}
