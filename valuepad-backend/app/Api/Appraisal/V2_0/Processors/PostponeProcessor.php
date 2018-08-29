<?php
namespace ValuePad\Api\Appraisal\V2_0\Processors;

use ValuePad\Api\Support\BaseProcessor;

class PostponeProcessor extends BaseProcessor
{
	protected function configuration()
	{
		return [
			'explanation' => 'string',
			'notifyAppraiser' => 'bool'
		];
	}

	/**
	 * @return string
	 */
	public function getExplanation()
	{
		return $this->get('explanation');
	}

	/**
	 * @return bool
	 */
	public function notifyAppraiser()
	{
		return $this->get('notifyAppraiser', true);
	}
}
