<?php
namespace ValuePad\Api\Session\V2_0\Controllers;

use Ascope\Libraries\Validation\Error;
use Ascope\Libraries\Validation\ErrorsThrowableCollection;
use Illuminate\Http\Response;
use ValuePad\Api\Session\V2_0\Processors\AutoLoginTokensProcessor;
use ValuePad\Api\Session\V2_0\Processors\DeleteAllProcessor;
use ValuePad\Api\Session\V2_0\Processors\StoreProcessor;
use ValuePad\Api\Session\V2_0\Transformers\SessionTransformer;
use ValuePad\Api\Support\BaseController;
use ValuePad\Core\Session\Services\SessionService;
use ValuePad\Core\User\Exceptions\InvalidTokenException;
use ValuePad\Core\User\Exceptions\UserNotFoundException;

/**
 *
 *
 */
class SessionsController extends BaseController
{
    /**
     * @var SessionService
     */
    private $sessionService;

    /**
     * @param SessionService $sessionService
     */
    public function initialize(SessionService $sessionService)
    {
        $this->sessionService = $sessionService;
    }

    /**
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        return $this->resource->make($this->sessionService->get($id), $this->transformer(SessionTransformer::class));
    }

    /**
     * @param StoreProcessor $processor
     * @return Response
     */
    public function store(StoreProcessor $processor)
    {
		$autoLoginToken = $processor->getAutoLoginToken();

		if ($autoLoginToken){
			try {
				$session = $this->sessionService->createWithAutoLoginToken($processor->getAutoLoginToken());
			} catch (InvalidTokenException $ex){
				ErrorsThrowableCollection::throwError('autoLoginToken', new Error('invalid', $ex->getMessage()));
			}
		} else {
			$session = $this->sessionService->create($processor->createCredentials());
		}

        return $this->resource->make($session, $this->transformer(SessionTransformer::class));
    }

    /**
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        $this->sessionService->delete($id);

        return $this->resource->blank();
    }

    /**
     * @param DeleteAllProcessor $processor
     * @return Response
     */
    public function destroyAll(DeleteAllProcessor $processor)
    {
        $this->sessionService->deleteAllByUserId($processor->get('user'));

        return $this->resource->blank();
    }

	/**
	 * @param AutoLoginTokensProcessor $processor
	 * @return Response
	 */
	public function storeAutoLoginToken(AutoLoginTokensProcessor $processor)
	{
		try {
			$token = $this->sessionService->createAutoLoginToken($processor->getUser());
		} catch (UserNotFoundException $ex){
			$errors = new ErrorsThrowableCollection();

			$errors['user'] = new Error('user-not-found', $ex->getMessage());

			throw $errors;
		}

		return $this->resource->make(['token' => $token->getValue()]);
	}

    /**
     * @param int $id
     * @return Response
     */
    public function refresh($id)
    {
        return $this->resource->make($this->sessionService->refresh($id), $this->transformer(SessionTransformer::class));
    }

    /**
     * @param int $id
     * @param SessionService $sessionService
     * @return bool
     */
    public static function verifyAction($id, SessionService $sessionService)
    {
        return $sessionService->exists($id);
    }
}
