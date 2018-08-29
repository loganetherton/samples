<?php
namespace ValuePad\Core\Appraisal\Validation;

use Ascope\Libraries\Validation\Binder;
use Ascope\Libraries\Validation\Property;
use Ascope\Libraries\Validation\Rules\Blank;
use Ascope\Libraries\Validation\Rules\Length;
use Ascope\Libraries\Validation\Rules\Obligate;
use Ascope\Libraries\Validation\SourceHandlerInterface;
use ValuePad\Core\Appraisal\Validation\Rules\AdditionalDocumentTypeExists;
use ValuePad\Core\Customer\Entities\Customer;
use ValuePad\Core\Customer\Services\CustomerService;
use ValuePad\Core\Document\Validation\DocumentInflator;
use ValuePad\Core\Support\Service\ContainerInterface;

trait AdditionalDocumentValidatorTrait
{
	/**
	 * @param Binder $binder
	 * @param ContainerInterface $container
	 * @param Customer $customer
	 * @param array $options
	 */
	protected function defineAdditionalDocument(
		Binder $binder,
		ContainerInterface $container,
		Customer $customer,
		array $options = []
	)
	{
		if ($namespace = $path = array_take($options, 'namespace', '')){
			$namespace .= '.';
		}

		/**
		 * @var CustomerService $customerService
		 */
		$customerService = $container->get(CustomerService::class);

		$binder->bind($namespace.'type', function(Property $property) use ($customerService, $customer){
			$property
				->addRule(new AdditionalDocumentTypeExists($customerService, $customer));
		});

		$binder->bind($namespace.'label', function(Property $property){
			$property
				->addRule(new Blank())
				->addRule(new Length(1, 255));
		});

		$binder->bind($namespace.'label', function(Property $property){
			$property
				->addRule(new Obligate());
		})->when(function(SourceHandlerInterface $source) use ($namespace, $path){
			return $source->getValue($namespace.'type') === null && (!$path || $source->hasProperty($path));
		});

		$bundle = $binder
			->bind($namespace.'document', (new DocumentInflator($container))->setRequired(true));

		if ($path){
			$bundle->when(function(SourceHandlerInterface $source) use ($path){
				return $source->hasProperty($path);
			});
		}
	}
}
