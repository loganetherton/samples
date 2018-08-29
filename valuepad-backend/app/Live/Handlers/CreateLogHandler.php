<?php
namespace ValuePad\Live\Handlers;

use ValuePad\Api\Assignee\V2_0\Transformers\LogTransformer;
use ValuePad\Core\Log\Notifications\CreateLogNotification;
use ValuePad\Core\User\Entities\User;
use RuntimeException;

class CreateLogHandler extends AbstractOrderHandler
{
	/**
	 * @return string
	 */
	protected function getName()
	{
		return 'create-log';
	}

	/**
	 * @param CreateLogNotification $notification
	 * @return User[]
	 */
	protected function getChannels($notification)
	{
	    if (!$notification instanceof CreateLogNotification){
            throw new RuntimeException('Unable to determine channels for the "'.get_class($notification).'" notification.');
        }

        $log = $notification->getLog();

        return $this->buildChannels($log->getAssignee(), $log->getCustomer());
	}

	/**
	 * @param CreateLogNotification $notification
	 * @return array
	 */
	protected function getData($notification)
	{
		return $this->transformer(LogTransformer::class)
			->transform($notification->getLog());
	}
}
