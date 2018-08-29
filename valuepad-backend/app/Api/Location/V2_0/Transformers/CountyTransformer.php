<?php
namespace ValuePad\Api\Location\V2_0\Transformers;

use ValuePad\Api\Support\BaseTransformer;
use ValuePad\Core\Location\Entities\County;

class CountyTransformer extends BaseTransformer
{
	/**
	 * @param County $county
	 * @return array
	 */
	public function transform($county)
	{
		return $this->extract($county);
	}
}
