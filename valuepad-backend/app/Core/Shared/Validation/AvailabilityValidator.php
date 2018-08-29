<?php
namespace ValuePad\Core\Shared\Validation;

use Ascope\Libraries\Converter\Transferer\Transferer;
use Ascope\Libraries\Validation\AbstractThrowableValidator;
use Ascope\Libraries\Validation\Binder;
use ValuePad\Core\Shared\Entities\AbstractAvailability;
use ValuePad\Core\Shared\Entities\Availability;
use ValuePad\Core\Shared\Entities\AvailabilityPerCustomer;
use ValuePad\Core\Shared\Persistables\AvailabilityPersistable;
use ValuePad\Core\Shared\Validation\Definers\AvailabilityDefiner;

class AvailabilityValidator extends AbstractThrowableValidator
{
	/**
	 * @param Binder $binder
	 * @return void
	 */
	protected function define(Binder $binder)
	{
        (new AvailabilityDefiner())->define($binder);
	}

	/**
	 * @param AvailabilityPersistable $source
	 * @param Availability|AvailabilityPerCustomer $availability
	 */
	public function validateWithAvailability(AvailabilityPersistable $source, AbstractAvailability $availability)
	{
		$persistable = new AvailabilityPersistable();

		(new Transferer(['ignore' => ['customer', 'user']]))->transfer($availability, $persistable);
		(new Transferer(
			['nullable' => $this->getForcedProperties()]
		))->transfer($source, $persistable);

		$this->validate($persistable);
	}
}
