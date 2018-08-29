<?php
namespace ValuePad\Core\User\Validation\Rules;

use Ascope\Libraries\Validation\Error;
use Ascope\Libraries\Validation\Rules\AbstractRule;
use Ascope\Libraries\Validation\Value;
use ValuePad\Core\User\Services\UserService;

/**
 *
 *
 */
class UsernameTaken extends AbstractRule
{

    /**
     *
     * @var UserService
     */
    private $userService;

    /**
     *
     * @var string
     */
    private $currentUsername;

    /**
     *
     * @param UserService $userService
     * @param string $currentUsername
     */
    public function __construct(UserService $userService, $currentUsername = null)
    {
        $this->userService = $userService;
        $this->currentUsername = $currentUsername;

        $this->setIdentifier('already-taken');
        $this->setMessage('Username is already taken.');
    }

    /**
     *
     * @param mixed|Value $value
     * @return Error|null
     */
    public function check($value)
    {
        if ($this->userService->existsWithUsername($value, $this->currentUsername)) {
            return $this->getError();
        }

        return null;
    }
}
