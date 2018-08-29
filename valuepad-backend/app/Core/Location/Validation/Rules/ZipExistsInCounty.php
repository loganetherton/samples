<?php
namespace ValuePad\Core\Location\Validation\Rules;

use Ascope\Libraries\Validation\Error;
use Ascope\Libraries\Validation\Rules\AbstractRule;
use Ascope\Libraries\Validation\Value;
use ValuePad\Core\Location\Services\CountyService;

class ZipExistsInCounty extends AbstractRule
{
	/**
	 * @var CountyService
	 */
	private $countyService;

	/**
	 * @param CountyService $countyService
	 */
	public function __construct(CountyService $countyService)
	{
		$this->countyService = $countyService;
		$this->setIdentifier('exists');
		$this->setMessage('One or more of the provided zip codes are not found.');
	}

	/**
	 * @param mixed|Value $value
	 * @return Error|null
	 */
	public function check($value)
	{
		list($zips, $county) = $value->extract();

		if (!$this->countyService->hasZips($county, $zips)){
			return $this->getError();
		}

		return null;
	}
}
