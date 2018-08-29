<?php
namespace ValuePad\Api\Session\V2_0\Processors;

use ValuePad\Api\Support\BaseProcessor;
use ValuePad\Core\User\Objects\Credentials;

/**
 *
 *
 */
class StoreProcessor extends BaseProcessor
{
	protected function configuration()
	{
		return [
			'username' => 'string',
			'password' => 'string',
			'autoLoginToken' => 'string'
		];
	}

    /**
     * @return Credentials
     */
    public function createCredentials()
    {
        return $this->populate(new Credentials());
    }

	/**
	 * @return string
	 */
	public function getAutoLoginToken()
	{
		return $this->get('autoLoginToken');
	}
}
