<?php
namespace ValuePad\Core\Company\Validation;

use Ascope\Libraries\Validation\AbstractThrowableValidator;
use Ascope\Libraries\Validation\Binder;
use ValuePad\Core\Company\Entities\Manager;
use ValuePad\Core\Company\Validation\Definers\ManagerDefiner;
use ValuePad\Core\Support\Service\ContainerInterface;


class ManagerValidator extends AbstractThrowableValidator
{
    /**
     * @var ManagerDefiner
     */
    private $definer;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->definer = new ManagerDefiner($container);
    }

    /**
     * @param Binder $binder
     */
    protected function define(Binder $binder)
    {
        $this->definer->define($binder);
    }

    /**
     * @param Manager $manager
     * @return $this
     */
    public function setCurrentManager(Manager $manager)
    {
        $this->definer->setCurrentManager($manager);
        return $this;
    }
}
