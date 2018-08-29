<?php
namespace ValuePad\Core\Appraisal\Persistables;

use ValuePad\Core\Document\Enums\Format;


class ExternalDocumentPersistable
{
	/**
	 * @var string
	 */
	private $url;
	public function setUrl($url) { $this->url = $url; }
	public function getUrl() { return $this->url; }

	/**
	 * @var string
	 */
	private $name;
	public function setName($name) { $this->name = $name; }
	public function getName() { return $this->name; }

	/**
	 * @var Format
	 */
	private $format;
	public function setFormat(Format $format) { $this->format = $format; }
	public function getFormat() { return $this->format; }

	/**
	 * @var int
	 */
	private $size;
	public function getSize() { return $this->size; }
	public function setSize($size) { $this->size = $size; }
}
