<?php
namespace ValuePad\Api\Support;

use Ascope\Libraries\Processor\AbstractProcessor;

class SelectableProcessor extends AbstractProcessor
{
	/**
	 * @return array
	 */
	public function getIds()
	{
		$ids = explode(',', $this->getRequest()->input('ids', ''));
		$ids = array_filter($ids, function($v){
			return is_numeric($v);
		});

		return array_map(function($v){ return (int) $v;}, $ids);
	}
}
