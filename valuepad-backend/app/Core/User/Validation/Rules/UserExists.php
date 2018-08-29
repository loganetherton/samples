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
class UserExists extends AbstractRule
{
    /**
     * @var UserService
     */
    private $userService;

    /**
     * @param UserService $userService
     */
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;

        $this->setMessage('User does not exist.');
        $this->setIdentifier('does-not-exist');
    }

    /**
     *
     * @param mixed|Value $value
     * @return Error|null
     */
    public function check($value)
    {
        if (! $this->userService->exists($value)) {
            return $this->getError();
        }

        return null;
    }
}
