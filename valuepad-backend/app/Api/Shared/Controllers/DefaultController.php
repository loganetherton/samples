<?php
namespace ValuePad\Api\Shared\Controllers;

use Illuminate\Routing\Controller;

class DefaultController extends Controller
{
	/**
	 * @return string
	 */
	public function server()
	{
		return 'You\'ve reached the ValuePad API server.';
	}

	/**
	 * @return string
	 */
	public function api()
	{
		return 'You\'ve reached the ValuePad API v2.0 server.';
	}
}
