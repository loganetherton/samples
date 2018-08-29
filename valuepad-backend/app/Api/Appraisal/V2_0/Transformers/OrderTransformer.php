<?php
namespace ValuePad\Api\Appraisal\V2_0\Transformers;

use ValuePad\Api\Support\BaseTransformer;
use ValuePad\Core\Appraisal\Entities\Order;

class OrderTransformer extends BaseTransformer
{
	/**
	 * @param Order $order
	 * @return array
	 */
	public function transform($order)
	{
		return $this->extract($order);
	}
}
