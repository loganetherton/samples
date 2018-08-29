<?php
namespace ValuePad\Core\Amc\Validation;
use Ascope\Libraries\Validation\AbstractThrowableValidator;
use Ascope\Libraries\Validation\Binder;
use Ascope\Libraries\Validation\Property;
use Ascope\Libraries\Validation\Rules\Blank;
use Ascope\Libraries\Validation\Rules\Length;
use Ascope\Libraries\Validation\Rules\Obligate;
use ValuePad\Core\Amc\Entities\Amc;
use ValuePad\Core\Location\Services\StateService;
use ValuePad\Core\Location\Validation\Definer\LocationDefiner;
use ValuePad\Core\Shared\Interfaces\EnvironmentInterface;
use ValuePad\Core\Shared\Validation\Rules\Phone;
use ValuePad\Core\Support\Service\ContainerInterface;
use ValuePad\Core\User\Services\UserService;
use ValuePad\Core\User\Validation\Inflators\EmailInflator;
use ValuePad\Core\User\Validation\Inflators\PasswordInflator;
use ValuePad\Core\User\Validation\Inflators\UsernameInflator;

class AmcValidator extends AbstractThrowableValidator
{
    /**
     * @var StateService
     */
    private $stateService;

    /**
     * @var UserService
     */
    private $userService;

    /**
     * @var EnvironmentInterface
     */
    private $environment;

    /**
     * @var Amc
     */
    private $currentAmc;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->stateService = $container->get(StateService::class);
        $this->userService = $container->get(UserService::class);
        $this->environment = $container->get(EnvironmentInterface::class);
    }

    /**
     * @param Binder $binder
     * @return void
     */
    protected function define(Binder $binder)
    {
        $binder->bind('companyName', function(Property $property){
            $property
                ->addRule(new Obligate())
                ->addRule(new Blank())
                ->addRule(new Length(1, 255));
        });

        $binder->bind('username', new UsernameInflator($this->userService, $this->environment, $this->currentAmc));
        $binder->bind('password', new PasswordInflator($this->environment));
        $binder->bind('email', new EmailInflator());

        (new LocationDefiner($this->stateService))->define($binder);

        $binder->bind('phone', function(Property $property){
            $property
                ->addRule(new Obligate())
                ->addRule(new Phone());
        });

        $binder->bind('fax', function(Property $property){
            $property->addRule(new Phone());
        });
    }

    /**
     * @param Amc $amc
     * @return $this
     */
    public function setCurrentAmc(Amc $amc)
    {
        $this->currentAmc = $amc;

        return $this;
    }
}
