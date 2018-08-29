<?php
namespace ValuePad\Letter\Support;

use ValuePad\Core\Support\Letter\LetterPreferenceInterface;
use Illuminate\Contracts\Config\Repository as Config;

class LetterPreference implements LetterPreferenceInterface
{
	/**
	 * @var Config
	 */
	private $config;

	/**
	 * @param Config $config
	 */
	public function __construct(Config $config)
	{
		$this->config = $config;
	}

	/**
	 * @return string
	 */
	public function getNoReply()
	{
		return $this->config->get('mail.no_reply');
	}

	/**
	 * @return string
	 */
	public function getSignature()
	{
		return $this->config->get('mail.signature', 'The ValuePad Team');
	}
}
