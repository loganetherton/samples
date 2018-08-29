<?php
namespace ValuePad\Core\Shared\Properties;

trait CommentPropertyTrait
{
	/**
	 * @var string
	 */
	private $comment;

	/**
	 * @param string $comment
	 */
	public function setComment($comment)
	{
		$this->comment = $comment;
	}

	/**
	 * @return string
	 */
	public function getComment()
	{
		return $this->comment;
	}
}
