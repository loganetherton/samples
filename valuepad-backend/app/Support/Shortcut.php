<?php
namespace ValuePad\Support;

use ValuePad\Core\Document\Entities\Document;
use ValuePad\Core\Document\Interfaces\DocumentPreferenceInterface;
use DateTime;
use DateTimeZone;

class Shortcut
{
	/**
	 * @param Document $document
	 * @param DocumentPreferenceInterface $preference
	 * @param bool $encoded
	 * @return string
	 */
	public static function extractUrlFromDocument(
		Document $document,
		DocumentPreferenceInterface $preference,
		$encoded = true
	)
	{
		if ($document->isExternal()){
			return $document->getUri();
		}

		$parts = pathinfo($document->getUri());

		$basename = $parts['basename'];

		if ($encoded){
			$basename = urlencode($basename);
		}

		return $preference->getBaseUrl() . $parts['dirname'].'/'.$basename;
	}

	/**
	 * @param string $prefix
	 * @return string
	 */
	public static function prependGlobalRoutePrefix($prefix)
	{
		$global = env('GLOBAL_ROUTE_PREFIX');

		if (!$global){
			return $prefix;
		}

		return $global.'/'.$prefix;
	}

	/**
	 * @param string $datetime
	 * @return DateTime
	 */
	public static function utc($datetime)
	{
		$datetime = new DateTime($datetime);

		$datetime->setTimezone(new DateTimeZone('UTC'));

		return $datetime;
	}
}
