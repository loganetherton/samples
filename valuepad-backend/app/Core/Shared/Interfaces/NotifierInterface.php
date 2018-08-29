<?php
namespace ValuePad\Core\Shared\Interfaces;

interface NotifierInterface
{
	/**
	 * @param object $notification
	 */
	public function notify($notification);
}
