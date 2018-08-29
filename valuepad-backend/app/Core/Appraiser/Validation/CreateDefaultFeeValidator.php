<?php
namespace ValuePad\Core\Appraiser\Validation;

use Ascope\Libraries\Validation\Binder;
use Ascope\Libraries\Validation\Property;
use Ascope\Libraries\Validation\Rules\Obligate;
use ValuePad\Core\Appraiser\Entities\Appraiser;
use ValuePad\Core\Appraiser\Services\AppraiserService;
use ValuePad\Core\Appraiser\Validation\Rules\JobTypeAvailableForDefaultFee;
use ValuePad\Core\Assignee\Validation\AbstractFeeValidator;
use ValuePad\Core\JobType\Services\JobTypeService;
use ValuePad\Core\JobType\Validation\Rules\JobTypeExists;
use ValuePad\Core\Support\Service\ContainerInterface;

class CreateDefaultFeeValidator extends AbstractFeeValidator
{
	/**
	 * @var JobTypeService
	 */
	private $jobTypeService;

	/**
	 * @var AppraiserService
	 */
	private $appraiserService;

	/**
	 * @var Appraiser
	 */
	private $appraiser;

	public function __construct(ContainerInterface $container, Appraiser $appraiser)
	{
		$this->appraiser = $appraiser;

		$this->jobTypeService = $container->get(JobTypeService::class);
		$this->appraiserService = $container->get(AppraiserService::class);
	}

	/**
	 * @param Binder $binder
	 * @return void
	 */
	protected function define(Binder $binder)
	{
		$binder->bind('jobType', function(Property $property){
			$property
				->addRule(new Obligate())
				->addRule(new JobTypeExists($this->jobTypeService))
				->addRule(new JobTypeAvailableForDefaultFee($this->appraiserService, $this->appraiser));
		});

		$this->defineAmount($binder);
	}
}
