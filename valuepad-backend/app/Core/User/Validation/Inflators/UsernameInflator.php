<?php
namespace ValuePad\Core\User\Validation\Inflators;
use Ascope\Libraries\Validation\Property;
use Ascope\Libraries\Validation\Rules\Blank;
use Ascope\Libraries\Validation\Rules\Obligate;
use ValuePad\Core\Shared\Interfaces\EnvironmentInterface;
use ValuePad\Core\User\Entities\User;
use ValuePad\Core\User\Services\UserService;
use ValuePad\Core\User\Validation\Rules\Username;
use ValuePad\Core\User\Validation\Rules\UsernameTaken;

class UsernameInflator
{
    /**
     * @var EnvironmentInterface
     */
    private $environment;

    /**
     * @var UserService
     */
    private $userService;

    /**
     * @var User
     */
    private $currentUser;

    /**
     * @param UserService $userService
     * @param EnvironmentInterface $environment
     * @param User $currentUser
     */
    public function __construct(UserService $userService, EnvironmentInterface $environment, User $currentUser = null)
    {
        $this->userService = $userService;
        $this->environment = $environment;
        $this->currentUser = $currentUser;
    }


    public function __invoke(Property $property)
    {
        $property
            ->addRule(new Obligate())
            ->addRule(new UsernameTaken($this->userService, object_take($this->currentUser, 'username')));

        if (!$this->environment->isRelaxed()){
            $property->addRule(new Username());
        } else {
            $property->addRule(new Blank());
        }
    }
}
