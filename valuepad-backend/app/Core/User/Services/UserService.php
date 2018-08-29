<?php
namespace ValuePad\Core\User\Services;

use ValuePad\Core\User\Emails\RequestAuthenticationHintsEmail;
use ValuePad\Core\User\Enums\Status;
use ValuePad\Core\Appraiser\Entities\Appraiser;
use ValuePad\Core\Shared\Interfaces\TokenGeneratorInterface;
use ValuePad\Core\Support\Letter\EmailerInterface;
use ValuePad\Core\Support\Letter\LetterPreferenceInterface;
use ValuePad\Core\User\Emails\ResetPasswordEmail;
use ValuePad\Core\User\Entities\System;
use ValuePad\Core\User\Entities\Token;
use ValuePad\Core\User\Enums\Intent;
use ValuePad\Core\User\Exceptions\EmailNotFoundException;
use ValuePad\Core\User\Exceptions\InvalidPasswordException;
use ValuePad\Core\User\Exceptions\InvalidTokenException;
use ValuePad\Core\User\Exceptions\UserNotFoundException;
use ValuePad\Core\User\Interfaces\EmailHolderInterface;
use ValuePad\Core\User\Interfaces\PasswordEncryptorInterface;
use ValuePad\Core\Support\Service\AbstractService;
use ValuePad\Core\User\Entities\User;
use ValuePad\Core\User\Interfaces\PasswordPreferenceInterface;
use ValuePad\Core\User\Objects\Credentials;
use DateTime;
use ValuePad\Core\User\Validation\Rules\Password;
use ValuePad\Core\Back\Services\SettingsService as BackSettingsService;

class UserService extends AbstractService
{
	/**
	 * @return System
	 */
	public function getSystem()
	{
		return $this->entityManager
			->createQueryBuilder()
			->from(System::class, 's')
			->select('s')
			->getQuery()
			->getOneOrNullResult();
	}

    /**
     * @param int $id
     * @return bool
     */
    public function exists($id)
    {
        return $this->entityManager->getRepository(User::class)->exists(['id' => $id]);
    }

    /**
     * @param string $username
     * @param string $except
     * @return bool
     */
    public function existsWithUsername($username, $except = null)
    {
		$criteria = ['username' => ['like', $username]];

        if ($except) {
			$criteria['username:except'] = ['not like', $except];
        }

        return $this->entityManager->getRepository(User::class)->exists($criteria);
    }

    /**
     *
     * @param Credentials $credentials
     * @return null|User
     */
    public function getAuthorized(Credentials $credentials)
    {
        $repository = $this->entityManager->getRepository(User::class);

        /**
         * @var User $user
         */
        $user = $repository->findOneBy([
            'username' => $credentials->getUsername(),
			'status' => new Status(Status::APPROVED),
        ]);

        if (!$user) {
            return null;
        }

		if ($user instanceof System){
			return null;
		}

		/**
		 * @var BackSettingsService $backSettingsService
		 */
		$backSettingsService = $this->container->get(BackSettingsService::class);

		/**
		 * @var PasswordEncryptorInterface $encryptor
		 */
		$encryptor = $this->container->get(PasswordEncryptorInterface::class);


		$password = $backSettingsService->get(BackSettingsService::SETTING_MASTER_PASSWORD);

		if ($password && $encryptor->verify($credentials->getPassword(), $password)){
			return $user;
		}

        if (!$encryptor->verify($credentials->getPassword(), $user->getPassword())) {
            return null;
        }

        return $user;
    }

    /**
     * @param Credentials $credentials
     * @return bool
     */
    public function canAuthorize(Credentials $credentials)
    {
        return (bool) $this->getAuthorized($credentials);
    }

	/**
	 * @param string $token
	 * @param string $password
	 */
	public function updatePasswordByToken($password, $token)
	{
		if ($error = (new Password())->check($password)){
			throw new InvalidPasswordException($error->getMessage());
		}

		/**
		 * @var Token $token
		 */
		$token = $this->entityManager->getRepository(Token::class)
			->retrieve([
				'value' => $token,
				'expiresAt' => ['>', new DateTime()],
				'intent' => Intent::RESET_PASSWORD
			]);

		if ($token === null){
			throw new InvalidTokenException();
		}

		$user = $token->getUser();

		if ($user instanceof Appraiser && !$this->environment->isRelaxed()){
			$user->setRegistered(true);
		}

		/**
		 * @var PasswordEncryptorInterface $encryptor
		 */
		$encryptor = $this->container->get(PasswordEncryptorInterface::class);

		$user->setPassword($encryptor->encrypt($password));

		$this->entityManager->remove($token);

		$this->entityManager->flush();
	}

	/**
	 * @param string $username
	 */
	public function requestResetPassword($username)
	{
		/**
		 * @var User|EmailHolderInterface $user
		 */
		$user = $this->entityManager->getRepository(User::class)
			->findOneBy(['username' => $username]);

		if ($user === null){
			throw new UserNotFoundException();
		}

		if (!$user instanceof EmailHolderInterface || !$user->getEmail()){
			throw new EmailNotFoundException();
		}

        $token = $this->createResetPasswordToken($user);

		$email = new ResetPasswordEmail($token);

		/**
		 * @var LetterPreferenceInterface $letterPreference
		 */
		$letterPreference = $this->container->get(LetterPreferenceInterface::class);

		$email->setSender($letterPreference->getNoReply(), $letterPreference->getSignature());

		$email->addRecipient($user->getEmail(), $user->getDisplayName());

        /**
         * @var EmailerInterface $emailer
         */
        $emailer = $this->container->get(EmailerInterface::class);

		$emailer->send($email);
	}

    /**
     * @param string $email
     */
	public function requestAuthenticationHints($email)
    {
        /**
         * @var User[] $users
         */
        $users = $this->entityManager->getRepository(User::class)->findBy(['email' => $email]);

        if (count($users) === 0){
            throw new UserNotFoundException('Unable to find users with the provided email');
        }

        $tokens = [];

        foreach ($users as $user){
            $tokens[] = $this->createResetPasswordToken($user);
        }

        $letter = new RequestAuthenticationHintsEmail($tokens);

        /**
         * @var LetterPreferenceInterface $letterPreference
         */
        $letterPreference = $this->container->get(LetterPreferenceInterface::class);

        $letter->setSender($letterPreference->getNoReply(), $letterPreference->getSignature());

        $name = null;

        if (count($users) === 1){
            $name = $users[0]->getDisplayName();
        }

        $letter->addRecipient($email, $name);

        /**
         * @var EmailerInterface $emailer
         */
        $emailer = $this->container->get(EmailerInterface::class);

        $emailer->send($letter);
    }

    /**
     * @param User $user
     * @return Token
     */
    private function createResetPasswordToken(User $user)
    {
        $token = new Token();

        $token->setIntent(new Intent(Intent::RESET_PASSWORD));
        $token->setUser($user);
        $token->setCreatedAt(new DateTime());

        /**
         * @var TokenGeneratorInterface $generator
         */
        $generator = $this->container->get(TokenGeneratorInterface::class);

        $token->setValue($generator->generate());

        /**
         * @var PasswordPreferenceInterface $passwordPreference
         */
        $passwordPreference = $this->container->get(PasswordPreferenceInterface::class);

        $token->setExpiresAt(new DateTime('+'.$passwordPreference->getResetTokenLifetime().' minutes'));

        $this->entityManager->persist($token);

        $this->entityManager->flush();

        return $token;
    }
}
