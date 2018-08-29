<?php
namespace ValuePad\DAL\Asc\Support\Import;

use FilterIterator;

class ProducerFilter extends FilterIterator
{
	public function accept()
	{
		/**
		 * @var Producer $producer
		 */
		$producer = $this->getInnerIterator();

		return $producer->isActive();
	}
}
