<?php
namespace ValuePad\Live\Handlers;

class CreateOrderHandler extends AbstractDataAwareOrderHandler
{
	/**
	 * @return string
	 */
	protected function getName()
	{
		return 'create';
	}
}
