<?php
namespace ValuePad\Mobile\Support;

interface HandlerInterface
{
	/**
	 * @param object $notification
	 * @return Tuple
	 */
	public function handle($notification);
}
