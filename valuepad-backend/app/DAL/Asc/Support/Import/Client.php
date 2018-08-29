<?php
namespace ValuePad\DAL\Asc\Support\Import;

use Goutte\Client as Goutte;
use GuzzleHttp\Cookie\CookieJar;
use Symfony\Component\DomCrawler\Link;

class Client extends Goutte
{
	/**
	 * @param Link $link
	 * @param string $dest
	 */
	public function download(Link $link, $dest)
	{
		$uri = $this->getAbsoluteUri($link->getUri());

		$cookies = CookieJar::fromArray(
			$this->getCookieJar()->allRawValues($uri),
			parse_url($uri, PHP_URL_HOST)
		);

		$this->getClient()->request('GET', $uri, [
			'cookies' => $cookies,
			'allow_redirects' => false,
			'save_to' => $dest
		]);
	}
}
