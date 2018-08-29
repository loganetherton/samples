<?php
namespace ValuePad\Core\Session\Validation;

use Ascope\Libraries\Validation\AbstractThrowableValidator;
use Ascope\Libraries\Validation\Binder;
use Ascope\Libraries\Validation\Property;
use Ascope\Libraries\Validation\Rules\Blank;
use Ascope\Libraries\Validation\Rules\Obligate;
use ValuePad\Core\Support\Service\ContainerInterface;
use ValuePad\Core\User\Services\UserService;
use ValuePad\Core\User\Validation\Rules\Access;

/**
 *
 *
 */
class CredentialsValidator extends AbstractThrowableValidator
{

    /**
     * @var ContainerInterface
     */
    private $userService;

    /**
     *
     * @param UserService $userService
     */
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     *
     * @param Binder $binder
     */
    protected function define(Binder $binder)
    {
        $binder->bind('username', function (Property $property) {

            $property->addRule(new Obligate())
                ->addRule(new Blank());
        });

        $binder->bind('password', function (Property $property) {

            $property->addRule(new Obligate())
                ->addRule(new Blank());
        });

        $binder->bind('credentials', ['username', 'password'], function (Property $property) {
            $property->addRule(new Access($this->userService));
        });
    }
}
