<?php
namespace ValuePad\Core\Company\Validation;
use Ascope\Libraries\Validation\Binder;
use ValuePad\Core\Company\Validation\Definers\ManagerDefiner;

class ManagerAsStaffValidator extends StaffValidator
{
    protected function define(Binder $binder)
    {
        parent::define($binder);

        $manager = new ManagerDefiner($this->container);
        $manager->setNamespace('user');

        $manager->define($binder);

    }
}
