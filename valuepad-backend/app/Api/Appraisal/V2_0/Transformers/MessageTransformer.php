<?php
namespace ValuePad\Api\Appraisal\V2_0\Transformers;

use ValuePad\Api\Support\BaseTransformer;
use ValuePad\Core\Appraisal\Entities\Message;

class MessageTransformer extends BaseTransformer
{
	/**
	 * @param Message $message
	 * @return array
	 */
	public function transform($message)
	{
		return $this->extract($message);
	}
}
