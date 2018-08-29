<?php
namespace ValuePad\Core\Shared\Properties;

trait AdditionalCommentsPropertyTrait
{
	/**
	 * @var string
	 */
	private $additionalComments;

	/**
	 * @param string $comments
	 */
	public function setAdditionalComments($comments)
	{
		$this->additionalComments = $comments;
	}

	/**
	 * @return string
	 */
	public function getAdditionalComments()
	{
		return $this->additionalComments;
	}
}
