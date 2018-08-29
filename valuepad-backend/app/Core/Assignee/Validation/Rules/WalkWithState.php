<?php
namespace ValuePad\Core\Assignee\Validation\Rules;

use Ascope\Libraries\Validation\Error;
use Ascope\Libraries\Validation\Rules\AbstractRule;
use Ascope\Libraries\Validation\Rules\Walk;
use Ascope\Libraries\Validation\Value;
use ValuePad\Core\Assignee\Interfaces\CoveragePersistableInterface;

class WalkWithState extends AbstractRule
{
	/**
	 * @var Walk
	 */
	private $walk;

	/**
	 * @param callable $inflator
	 */
	public function __construct(callable  $inflator)
	{
		$this->walk = new Walk($inflator);
	}

	/**
	 * @return Error
	 */
	public function getError()
	{
		return $this->walk->getError();
	}

	/**
	 * @param mixed|Value $value
	 * @return Error|null
	 */
	public function check($value)
	{
		/**
		 * @var CoveragePersistableInterface[] $coverages
		 */
		list($coverages, $state) = $value->extract();

		$data = [];

		foreach ($coverages as $coverage){
			$data[] = [
				'zips' => $coverage->getZips(),
				'county' => $coverage->getCounty(),
				'state' => $state
			];
		}

		$error = $this->walk->check($data);

		if ($error){
			return $this->getError();
		}

		return null;
	}
}
