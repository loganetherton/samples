<?php
namespace ValuePad\Push\Handlers\Customer\Appraisal;

use Ascope\Libraries\Converter\Extractor\Extractor;
use Ascope\Libraries\Modifier\Manager;
use Ascope\Libraries\Transformer\SharedModifiers;
use ValuePad\Core\Appraisal\Notifications\AcceptOrderWithConditionsNotification;

class AcceptOrderWithConditionsHandler extends BaseHandler
{
	/**
	 * @param AcceptOrderWithConditionsNotification $notification
	 * @return array
	 */
	protected function transform($notification)
	{
		$manager = new Manager();
		$manager->registerProvider(new SharedModifiers());

		return [
			'type' => 'order',
			'event' => 'accept-with-conditions',
			'order' => $notification->getOrder()->getId(),
			'conditions' => (new Extractor())
				->setModifierManager($manager)
				->extract($notification->getConditions())
		];
	}
}
