<?php
namespace ValuePad\Core\Document\Interfaces;

interface DocumentPreferenceInterface
{
    /**
     * @return int
     */
    public function getLifetime();

	/**
	 * @return string
	 */
	public function getBaseUrl();
}
