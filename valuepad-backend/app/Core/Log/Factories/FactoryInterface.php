<?php
namespace ValuePad\Core\Log\Factories;

use ValuePad\Core\Log\Entities\Log;

interface FactoryInterface
{
	/**
	 * @param object $notification
	 * @return Log
	 */
	public function create($notification);
}
