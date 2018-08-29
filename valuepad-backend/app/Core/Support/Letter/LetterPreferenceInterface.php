<?php
namespace ValuePad\Core\Support\Letter;

interface LetterPreferenceInterface
{
	/**
	 * @return string
	 */
	public function getNoReply();

	/**
	 * @return string
	 */
	public function getSignature();
}
