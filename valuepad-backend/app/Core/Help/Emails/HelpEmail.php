<?php
namespace ValuePad\Core\Help\Emails;

use ValuePad\Core\Support\Letter\Email;

abstract class HelpEmail extends Email
{
	/**
	 * @var string
	 */
	private $description;

	/**
	 * @param string $description
	 */
	public function __construct($description)
	{
		$this->description = $description;
	}

	/**
	 * @return string
	 */
	public function getDescription()
	{
		return $this->description;
	}
}
