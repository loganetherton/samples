<?php
namespace ValuePad\Live\Support;

interface HandlerInterface
{
	/**
	 * @param object $notification
	 * @return Event
	 */
	public function handle($notification);
}
