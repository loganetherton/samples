<?php
namespace ValuePad\Core\Document\Persistables;

use ValuePad\Core\Document\Properties\FormatPropertyTrait;
use ValuePad\Core\Document\Properties\SizePropertyTrait;
use ValuePad\Core\Shared\Properties\NamePropertyTrait;

class ExternalDocumentPersistable
{
	use NamePropertyTrait;
	use FormatPropertyTrait;
	use SizePropertyTrait;

	/**
	 * @var string
	 */
	private $url;

	/**
	 * @param string $url
	 */
	public function setUrl($url)
	{
		$this->url = $url;
	}

	/**
	 * @return string
	 */
	public function getUrl()
	{
		return $this->url;
	}
}
