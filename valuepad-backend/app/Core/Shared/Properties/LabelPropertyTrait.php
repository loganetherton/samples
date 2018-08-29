<?php
namespace ValuePad\Core\Shared\Properties;

trait LabelPropertyTrait
{
	/**
	 * @var string
	 */
	private $label;

	/**
	 * @param string $label
	 */
	public function setLabel($label)
	{
		$this->label = $label;
	}

	/**
	 * @return string
	 */
	public function getLabel()
	{
		return $this->label;
	}
}
