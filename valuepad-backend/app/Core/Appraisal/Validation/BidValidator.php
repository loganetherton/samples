<?php
namespace ValuePad\Core\Appraisal\Validation;

use Ascope\Libraries\Validation\AbstractThrowableValidator;
use Ascope\Libraries\Validation\Binder;
use Ascope\Libraries\Validation\Property;
use Ascope\Libraries\Validation\Rules\Callback;
use Ascope\Libraries\Validation\Rules\Each;
use Ascope\Libraries\Validation\Rules\Greater;
use Ascope\Libraries\Validation\Rules\Length;
use Ascope\Libraries\Validation\Rules\Obligate;
use DateTime;
use ValuePad\Core\Appraisal\Options\RequireEstimatedCompletionDateOptionTrait;
use ValuePad\Core\Appraisal\Validation\Inflators\BidValidAppraisersInflator;
use ValuePad\Core\Appraiser\Services\AppraiserService;
use ValuePad\Core\Company\Entities\Company;
use ValuePad\Core\Company\Services\CompanyService;
use ValuePad\Core\Company\Validation\Rules\AppraiserInCompany;
use ValuePad\Core\Shared\Interfaces\EnvironmentInterface;
use ValuePad\Core\Support\Service\ContainerInterface;
use ValuePad\Core\User\Validation\Rules\UserIsAppraiser;
use ValuePad\IoC\Container;

class BidValidator extends AbstractThrowableValidator
{
	use RequireEstimatedCompletionDateOptionTrait;

	/**
	 * @var EnvironmentInterface
	 */
	private $environment;

	/**
	 * @var CompanyService
	 */
	private $companyService;

	/**
	 * @var AppraiserService
	 */
	private $appraiserService;

	/**
	 * @var bool
	 */
	private $validateAppraisers;

	/**
	 * @var Company
	 */
	private $company;

	/**
	 * @param EnvironmentInterface $environment
	 * @param ContainerInterface $container
	 */
	public function __construct(EnvironmentInterface $environment, ContainerInterface $container)
	{
		$this->environment = $environment;
		$this->companyService = $container->get(CompanyService::class);
		$this->appraiserService = $container->get(AppraiserService::class);
	}

	/**
	 * @param Binder $binder
	 * @return void
	 */
	protected function define(Binder $binder)
	{
		$binder->bind('amount', function(Property $property){
			$property
				->addRule(new Obligate())
				->addRule(new Greater(0));
		});

		$binder->bind('estimatedCompletionDate', function(Property $property){

			if ($this->isEstimatedCompletionDateRequired() && !$this->environment->isRelaxed()){
				$property->addRule(new Obligate());
			}

			if (!$this->environment->isRelaxed()){
				$property->addRule(new Greater(new DateTime()));
			}
		});

		$binder->bind('comments', function(Property $property){
			$property
				->addRule(new Length(1, 255));
		});

		if ($this->validateAppraisers) {
			$company = $this->company;
			$companyService = $this->companyService;
			$appraiserService = $this->appraiserService;
			$binder->bind('appraisers', function (Property $property) use ($company, $companyService, $appraiserService) {
				$property
					->addRule(new Each(function () use ($company, $companyService, $appraiserService) {
						return new BidValidAppraisersInflator(
							new UserIsAppraiser($appraiserService),
							new AppraiserInCompany($companyService, $company)
						);
					}));

				$property
					->addRule(
						(new Callback([$this, 'uniqueAppraisers']))
							->setIdentifier('unique')
							->setMessage('The provided appraisers must be unique.')
					);
			});
		}
	}

	/**
	 * Sets the flag that determines whether the appraisers attribute should be validated
	 *
	 * @param bool $flag
	 * @return $this
	 */
	public function setValidateAppraisers($flag)
	{
		$this->validateAppraisers = $flag;

		return $this;
	}

	/**
	 * Sets the company that'll be used when validation appraisers
	 *
	 * @param Company $company
	 * @return $this
	 */
	public function setCompany($company)
	{
		$this->company = $company;

		return $this;
	}

	/**
	 * Checks to see if there are any duplicates in the appraiser list
	 *
	 * @param int[] $appraisers
	 * @return bool
	 */
	public function uniqueAppraisers(array $appraisers)
	{
		return count(array_unique($appraisers)) === count($appraisers);
	}
}
