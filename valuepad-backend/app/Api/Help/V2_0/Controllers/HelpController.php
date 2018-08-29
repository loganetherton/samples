<?php
namespace ValuePad\Api\Help\V2_0\Controllers;

use Ascope\Libraries\Validation\Error;
use Ascope\Libraries\Validation\ErrorsThrowableCollection;
use Illuminate\Http\Response;
use ValuePad\Api\Help\V2_0\Processors\HelpProcessor;
use ValuePad\Api\Help\V2_0\Processors\HintsProcessor;
use ValuePad\Api\Support\BaseController;
use ValuePad\Core\Help\Emails\IssueEmail;
use ValuePad\Core\Help\Emails\RequestFeatureEmail;
use ValuePad\Core\Session\Entities\Session;
use ValuePad\Core\Support\Letter\EmailerInterface;
use ValuePad\Core\Support\Letter\LetterPreferenceInterface;
use ValuePad\Core\User\Exceptions\UserNotFoundException;
use ValuePad\Core\User\Interfaces\EmailHolderInterface;
use ValuePad\Core\User\Services\UserService;

class HelpController extends BaseController
{
	const EMAIL = 'support@appraisalscope.com';

	/**
	 * @var EmailerInterface
	 */
	private $emailer;

	/**
	 * @var LetterPreferenceInterface
	 */
	private $preference;

	/**
	 * @param EmailerInterface $emailer
	 * @param LetterPreferenceInterface $preference
	 */
	public function initialize(EmailerInterface $emailer, LetterPreferenceInterface $preference)
	{
		$this->emailer = $emailer;
		$this->preference = $preference;
	}

	/**
	 * @param HelpProcessor $processor
	 * @return Response
	 */
	public function storeIssues(HelpProcessor $processor)
	{
		$email = new IssueEmail($processor->getDescription());

		$sender = $this->getSender();
		$email->setSender($sender[0], $sender[1]);

		$email->addRecipient(static::EMAIL);

		$this->emailer->send($email);

		return $this->resource->blank();
	}

	/**
	 * @param HelpProcessor $processor
	 * @return Response
	 */
	public function storeFeatureRequests(HelpProcessor $processor)
	{
		$email = new RequestFeatureEmail($processor->getDescription());

		$sender = $this->getSender();
		$email->setSender($sender[0], $sender[1]);

		$email->addRecipient(static::EMAIL);

		$this->emailer->send($email);

		return $this->resource->blank();
	}


    /**
     * @param HintsProcessor $processor
     * @return Response
     */
	public function hints(HintsProcessor $processor)
    {
        /**
         * @var UserService $userService
         */
        $userService = $this->container->make(UserService::class);

       try {
           $userService->requestAuthenticationHints($processor->getEmail());
       } catch (UserNotFoundException $ex){
           $errors = new ErrorsThrowableCollection();

           $errors['email'] = new Error('not-found', $ex->getMessage());

           throw $errors;
       }

        return $this->resource->blank();
    }

	/**
	 * @return array
	 */
	private function getSender()
	{
		/**
		 * @var Session $session
		 */
		$session = $this->container->make(Session::class);

		$user = $session->getUser();

        $name = $user->getDisplayName();

        if ($user instanceof EmailHolderInterface){
            $email = $user->getEmail();
        } else {
            $email = $this->preference->getNoReply();
        }

		return [$email, $name];
	}
}
