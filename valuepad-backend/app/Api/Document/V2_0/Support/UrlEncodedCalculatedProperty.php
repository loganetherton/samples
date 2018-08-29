<?php
namespace ValuePad\Api\Document\V2_0\Support;

use ValuePad\Core\Document\Entities\Document;
use ValuePad\Core\Document\Interfaces\DocumentPreferenceInterface;
use ValuePad\Support\Shortcut;

class UrlEncodedCalculatedProperty
{
	/**
	 * @var DocumentPreferenceInterface
	 */
	private $preference;

	/**
	 * @param DocumentPreferenceInterface $preference
	 */
    public function __construct(DocumentPreferenceInterface $preference)
    {
        $this->preference = $preference;
    }

    /**
     * @param Document $document
     * @return string
     */
    public function __invoke(Document $document)
    {
		return Shortcut::extractUrlFromDocument($document, $this->preference);
    }
}
