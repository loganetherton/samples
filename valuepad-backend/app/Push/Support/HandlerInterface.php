<?php
namespace ValuePad\Push\Support;

interface HandlerInterface
{
	/**
	 * @param object $notification
	 * @return Payload
	 */
	public function handle($notification);
}
