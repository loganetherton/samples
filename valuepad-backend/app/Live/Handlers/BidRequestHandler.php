<?php
namespace ValuePad\Live\Handlers;

class BidRequestHandler extends AbstractDataAwareOrderHandler
{
	/**
	 * @return string
	 */
	protected function getName()
	{
		return 'bid-request';
	}
}
