<?php
namespace ValuePad\Core\Appraiser\Options;

use ValuePad\Core\Shared\Options\UpdateOptions;

class UpdateAppraiserOptions extends UpdateOptions
{
	/**
	 * @var bool
	 */
	private $isSoftValidationMode = false;

	/**
	 * @param bool $flag
	 */
	public function setSoftValidationMode($flag)
	{
		$this->isSoftValidationMode = $flag;
	}

	/**
	 * @return bool
	 */
	public function isSoftValidationMode()
	{
		return $this->isSoftValidationMode;
	}
}
