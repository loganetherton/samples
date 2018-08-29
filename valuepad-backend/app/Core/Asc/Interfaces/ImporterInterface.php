<?php
namespace ValuePad\Core\Asc\Interfaces;

use ValuePad\Core\Asc\Persistables\AppraiserPersistable;

interface ImporterInterface
{
	/**
	 * @return AppraiserPersistable[]
	 */
	public function import();
}
