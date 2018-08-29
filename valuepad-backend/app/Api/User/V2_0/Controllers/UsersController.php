<?php
namespace ValuePad\Api\User\V2_0\Controllers;

use Illuminate\Http\Response;
use ValuePad\Api\Support\BaseController;
use ValuePad\Core\User\Services\UserService;

class UsersController extends BaseController
{
	/**
	 * @var UserService
	 */
	private $userService;

	/**
	 * @param UserService $userService
	 */
	public function initialize(UserService $userService)
	{
		$this->userService = $userService;
	}

	public function show($username, Response $response)
	{
		if (!$this->userService->existsWithUsername($username)){
			return $this->resource->error()->notFound();
		}

		return $response->setStatusCode(Response::HTTP_OK);
	}

	/**
	 * @return bool
	 */
	public static function verifyAction(){
		return true;
	}
}
