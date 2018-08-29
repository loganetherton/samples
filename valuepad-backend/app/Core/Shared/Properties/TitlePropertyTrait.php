<?php
namespace ValuePad\Core\Shared\Properties;

trait TitlePropertyTrait
{
	/**
	 * @var string
	 */
	private $title;

	/**
	 * @param string $title
	 */
	public function setTitle($title)
	{
		$this->title = $title;
	}

	/**
	 * @return string
	 */
	public function getTitle()
	{
		return $this->title;
	}
}
