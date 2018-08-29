<?php
namespace ValuePad\Api\Help\V2_0\Controllers;

use Ascope\Libraries\Validation\Error;
use Ascope\Libraries\Validation\ErrorsThrowableCollection;
use Illuminate\Http\Response;
use ValuePad\Api\Help\V2_0\Processors\PasswordChangeProcessor;
use ValuePad\Api\Help\V2_0\Processors\PasswordResetProcessor;
use ValuePad\Api\Support\BaseController;
use ValuePad\Core\User\Exceptions\EmailNotFoundException;
use ValuePad\Core\User\Exceptions\InvalidPasswordException;
use ValuePad\Core\User\Exceptions\InvalidTokenException;
use ValuePad\Core\User\Exceptions\UserNotFoundException;
use ValuePad\Core\User\Services\UserService;

class PasswordController extends BaseController
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

	/**
	 * @param PasswordResetProcessor $processor
	 * @return Response
	 */
	public function reset(PasswordResetProcessor $processor)
	{
		try {
			$this->userService->requestResetPassword($processor->getUsername());
		} catch (UserNotFoundException $ex){
			$this->throwUsernameError('user-not-found', $ex->getMessage());
		} catch (EmailNotFoundException $ex){
			$this->throwUsernameError('email-not-found', $ex->getMessage());
		}

		return $this->resource->blank();
	}

	/**
	 * @param string $identifier
	 * @param string $message
	 */
	private function throwUsernameError($identifier, $message)
	{
		$errors = new ErrorsThrowableCollection();

		$errors['username'] = new Error($identifier, $message);

		throw $errors;
	}

	/**
	 * @param PasswordChangeProcessor $processor
	 * @return Response
	 */
	public function change(PasswordChangeProcessor $processor)
	{
		try {
			$this->userService->updatePasswordByToken($processor->getPassword(), $processor->getToken());
		} catch (InvalidPasswordException $ex){
			$errors = new ErrorsThrowableCollection();

			$errors['password'] = new Error('invalid', $ex->getMessage());

			throw $errors;
		} catch (InvalidTokenException $ex){
			$errors = new ErrorsThrowableCollection();

			$errors['token'] = new Error('invalid', $ex->getMessage());

			throw $errors;
		}

		return $this->resource->blank();
	}
}
