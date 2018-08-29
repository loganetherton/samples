<?php
namespace ValuePad\Core\Appraisal\Persistables;

class MessagePersistable
{
	/**
	 * @var string
	 */
	private $content;
	public function setContent($content) { $this->content = $content; }
	public function getContent() { return $this->content; }
}
