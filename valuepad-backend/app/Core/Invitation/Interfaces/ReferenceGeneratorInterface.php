<?php
namespace ValuePad\Core\Invitation\Interfaces;

interface ReferenceGeneratorInterface
{
	/**
	 * @return string
	 */
	public function generate();
}
