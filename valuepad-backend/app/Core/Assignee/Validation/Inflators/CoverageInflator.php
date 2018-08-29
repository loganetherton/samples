<?php
namespace ValuePad\Core\Assignee\Validation\Inflators;

use Ascope\Libraries\Validation\Binder;
use Ascope\Libraries\Validation\Property;
use Ascope\Libraries\Validation\Rules\Obligate;
use Ascope\Libraries\Validation\SourceHandlerInterface;
use ValuePad\Core\Location\Services\CountyService;
use ValuePad\Core\Location\Services\StateService;
use ValuePad\Core\Location\Validation\Rules\CountyExistsInState;
use ValuePad\Core\Location\Validation\Rules\ZipExistsInCounty;

class CoverageInflator
{
	/**
	 * @var StateService
	 */
	private $stateService;

	/**
	 * @var CountyService
	 */
	private $countyService;

	/**
	 * @param StateService $stateService
	 * @param CountyService $countyService
	 */
	public function __construct(StateService $stateService, CountyService $countyService)
	{
		$this->stateService = $stateService;
		$this->countyService = $countyService;
	}

	/**
	 * @param Binder $binder
	 */
	public function __invoke(Binder $binder)
	{
		$binder->bind('county', ['county', 'state'], function(Property $property){
			$property
				->addRule(new Obligate())
				->addRule(new CountyExistsInState($this->stateService));
		});

		$binder->bind('zips', ['zips', 'county'], function(Property $property){
			$property
				->addRule(new ZipExistsInCounty($this->countyService));
		})->when(function(SourceHandlerInterface $source){
			return (bool) $source->getValue('zips');
		});
	}
}
