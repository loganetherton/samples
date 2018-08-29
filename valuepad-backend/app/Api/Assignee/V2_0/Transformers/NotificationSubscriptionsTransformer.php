<?php
namespace ValuePad\Api\Assignee\V2_0\Transformers;

use ValuePad\Api\Support\BaseTransformer;
use ValuePad\Core\Assignee\Entities\NotificationSubscription;

class NotificationSubscriptionsTransformer extends BaseTransformer
{
	/**
	 * @param NotificationSubscription[] $subscriptions
	 * @return array
	 */
	public function transform($subscriptions)
	{
		$data = [];

		foreach ($subscriptions as $subscription){
			$data[] = $this->extract($subscription);
		}

		return $data;
	}
}
