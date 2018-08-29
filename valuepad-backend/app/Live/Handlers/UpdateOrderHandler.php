<?php
namespace ValuePad\Live\Handlers;

class UpdateOrderHandler extends AbstractDataAwareOrderHandler
{
	/**
	 * @return string
	 */
	protected function getName()
	{
		return 'update';
	}
}
