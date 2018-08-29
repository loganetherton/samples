<?php
namespace ValuePad\Api\Support\Converter\Extractor;

use Ascope\Libraries\Converter\Extractor\Resolvers\AbstractResolver;
use Ascope\Libraries\Converter\Extractor\Root;
use Ascope\Libraries\Enum\Enum;
use Ascope\Libraries\Modifier\Manager;
use ValuePad\Core\Log\Extras\ExtraInterface;
use DateTime;

class ExtraResolver extends AbstractResolver
{
	/**
	 * @var Manager
	 */
	private $modifier;

	/**
	 * Checks whether the resolver can resolve a value
	 *
	 * @param string $scope
	 * @param Root $root
	 * @param mixed $value
	 * @return bool
	 */
	public function canResolve($scope, $value, Root $root = null)
	{
		return $value instanceof ExtraInterface;
	}

	/**
	 * Resolves a value
	 *
	 * @param string $scope
	 * @param Root $root
	 * @param ExtraInterface $value
	 * @return mixed
	 */
	public function resolve($scope, $value, Root $root = null)
	{
		return array_map_recursive(function($value){
			if ($value instanceof  DateTime){
				return $this->modifier->modify($value, 'datetime');
			}

			if ($value instanceof Enum){
				return (string) $value;
			}

			return $value;
		}, $value->getData());
	}

	/**
	 * @param Manager $manager
	 */
	public function setModifier(Manager $manager)
	{
		$this->modifier = $manager;
	}
}
