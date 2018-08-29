<?php
namespace ValuePad\Core\Session\Interfaces;

/**
 *
 * @author Sergei Melnikov <me@rnr.name>
 */
interface SessionPreferenceInterface
{
    /**
     * @return int
     */
    public function getLifetime();

	/**
	 * @return int
	 */
	public function getAutoLoginTokenLifetime();
}