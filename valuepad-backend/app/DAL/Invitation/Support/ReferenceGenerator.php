<?php
namespace ValuePad\DAL\Invitation\Support;

use ValuePad\Core\Invitation\Interfaces\ReferenceGeneratorInterface;

class ReferenceGenerator implements ReferenceGeneratorInterface
{
	/**
	 * @return string
	 */
	public function generate()
	{
		return str_random(64);
	}
}
