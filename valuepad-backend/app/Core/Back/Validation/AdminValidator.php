<?php
namespace ValuePad\Core\Back\Validation;
use Ascope\Libraries\Validation\AbstractThrowableValidator;
use Ascope\Libraries\Validation\Binder;
use ValuePad\Core\Back\Entities\Admin;
use ValuePad\Core\Shared\Interfaces\EnvironmentInterface;
use ValuePad\Core\Support\Service\ContainerInterface;
use ValuePad\Core\User\Services\UserService;
use ValuePad\Core\User\Validation\Inflators\EmailInflator;
use ValuePad\Core\User\Validation\Inflators\FirstNameInflator;
use ValuePad\Core\User\Validation\Inflators\LastNameInflator;
use ValuePad\Core\User\Validation\Inflators\PasswordInflator;
use ValuePad\Core\User\Validation\Inflators\UsernameInflator;

class AdminValidator extends AbstractThrowableValidator
{
    /**
     * @var UserService
     */
    private $userService;

    /**
     * @var EnvironmentInterface
     */
    private $environment;

    /**
     * @var Admin
     */
    private $currentAdmin;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->userService = $container->get(UserService::class);
        $this->environment = $container->get(EnvironmentInterface::class);
    }

    /**
     * @param Binder $binder
     * @return void
     */
    protected function define(Binder $binder)
    {
        $binder->bind('username', new UsernameInflator($this->userService, $this->environment, $this->currentAdmin));
        $binder->bind('password', new PasswordInflator($this->environment));
        $binder->bind('firstName', new FirstNameInflator());
        $binder->bind('lastName', new LastNameInflator());
        $binder->bind('email', new EmailInflator());
    }

    /**
     * @param Admin $admin
     * @return $this
     */
    public function setCurrentAdmin(Admin $admin)
    {
        $this->currentAdmin = $admin;

        return $this;
    }
}
