<?php
namespace ValuePad\Api\Document\V2_0\Protectors;

use Ascope\Libraries\Permissions\ProtectorInterface;
use Illuminate\Http\Request;

class FriendProtector implements ProtectorInterface
{
	/**
	 * @var Request
	 */
	private $request;

	public function __construct(Request $request)
	{
		$this->request = $request;
	}

	/**
	 * @return bool
	 */
	public function grants()
	{
		$identifier = $this->request->header('System-Identifier');

		return $identifier === 'muw0t5dFsRsQIMsJoiBr3vTlMunW1d8Z';
	}
}
