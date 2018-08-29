<?php
namespace ValuePad\Core\Asc\Validation\Rules;

use Ascope\Libraries\Validation\Error;
use Ascope\Libraries\Validation\Rules\AbstractRule;
use Ascope\Libraries\Validation\Value;
use ValuePad\Core\Asc\Services\AscService;

class AscAppraiserExists extends AbstractRule
{
	/**
	 * @var AscService $ascService
	 */
	private $ascService;

	/**
	 * @param AscService $ascService
	 */
	public function __construct(AscService $ascService)
	{
		$this->ascService = $ascService;

		$this->setIdentifier('exists');
		$this->setMessage('The provided appraiser does not exist in the asc database.');
	}

	/**
	 * @param mixed|Value $value
	 * @return Error|null
	 */
	public function check($value)
	{
		if (!$this->ascService->exists($value)){
			return $this->getError();
		}

		return null;
	}
}
