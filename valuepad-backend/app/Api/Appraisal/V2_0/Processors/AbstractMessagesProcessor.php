<?php
namespace ValuePad\Api\Appraisal\V2_0\Processors;

use ValuePad\Api\Support\BaseProcessor;

abstract class AbstractMessagesProcessor extends BaseProcessor
{
	/**
	 * @return array
	 */
	protected function configuration()
	{
		return ['content' => 'string'];
	}
}
