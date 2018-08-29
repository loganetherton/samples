<?php
namespace ValuePad\Api\Assignee\V2_0\Transformers;

use ValuePad\Api\Support\BaseTransformer;
use ValuePad\Core\Appraisal\Objects\Counter;

class CountersTransformer extends BaseTransformer
{
	/**
	 * @param Counter[] $counters
	 * @return array
	 */
	public function transform($counters)
	{
		$data = [];

		foreach ($counters as $counter){
			$data[camel_case((string) $counter->getQueue())] = $counter->getTotal();
		}

		return $data;
	}
}
