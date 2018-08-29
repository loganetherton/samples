<?php
namespace ValuePad\Live\Hook;

use Ascope\Libraries\Permissions\PermissionsManager;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller as BaseController;
use ValuePad\Live\Support\PusherWrapperInterface;

class Controller extends BaseController
{
	/**
	 * @param Request $request
	 * @param PusherWrapperInterface $pusher
	 * @param PermissionsManager $permissions
	 * @return Response
	 */
	public function auth(Request $request, PusherWrapperInterface $pusher, PermissionsManager $permissions)
	{
		$header = ['Content-Type' => 'application/json'];

		if (!$permissions->has(Protector::class)){
			return new Response('', Response::HTTP_FORBIDDEN, $header);
		}

		$content = $pusher->auth($request->input('channel_name'), $request->input('socket_id'));

		return new Response($content, Response::HTTP_OK, $header);
	}
}
