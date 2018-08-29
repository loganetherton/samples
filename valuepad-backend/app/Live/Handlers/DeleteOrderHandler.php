<?php
namespace ValuePad\Live\Handlers;

class DeleteOrderHandler extends AbstractDataAwareOrderHandler
{
	/**
	 * @return string
	 */
	protected function getName()
	{
		return 'delete';
	}
}
