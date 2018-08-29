<?php
namespace ValuePad\Api\Location\V2_0\Processors;

use Ascope\Libraries\Processor\AbstractProcessor;

class CountiesProcessor extends AbstractProcessor
{
	/**
	 * Indicates whether auto validation is allowed
	 *
	 * @return bool
	 */
	public function validateAutomatically()
	{
		return false;
	}

	/**
	 * @return array
	 */
	public function getSelectedCounties()
	{
		return $this->get('filter.counties', '', 'explode');
	}
}
