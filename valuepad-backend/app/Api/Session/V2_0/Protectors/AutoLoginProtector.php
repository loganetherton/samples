<?php
namespace ValuePad\Api\Session\V2_0\Protectors;

use Illuminate\Http\Request;
use ValuePad\Api\Shared\Protectors\AuthProtector;

class AutoLoginProtector extends AuthProtector
{
	const TOKEN = 'FrZcR8h87exMQvhDrD8CMEVLh7zznbPTdEGFpAEkrMW67QA9';
	const HEADER = 'X-Auto-Login-Token';

	public function grants()
	{
		if (!parent::grants()){
			return false;
		}

		/**
		 * @var Request $request
		 */
		$request = $this->container->make('request');

		$token = $request->header(static::HEADER);

		return $token === static::TOKEN;
	}
}
