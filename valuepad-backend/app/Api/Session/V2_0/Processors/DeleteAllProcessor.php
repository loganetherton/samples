<?php
namespace ValuePad\Api\Session\V2_0\Processors;

use Ascope\Libraries\Processor\AbstractProcessor;
use Ascope\Libraries\Validation\Binder;
use Ascope\Libraries\Validation\Property;
use Ascope\Libraries\Validation\Rules\IntegerCast;
use Ascope\Libraries\Validation\Rules\Obligate;
use ValuePad\Core\User\Services\UserService;
use ValuePad\Core\User\Validation\Rules\UserExists;

/**
 *
 *
 */
class DeleteAllProcessor extends AbstractProcessor
{
    /**
     * @param Binder $binder
     * @return void
     */
    protected function rules(Binder $binder)
    {
        $binder->bind('user', function (Property $property) {
            $property->addRule(new Obligate())
                ->addRule(new IntegerCast(true))
                ->addRule(new UserExists($this->container->make(UserService::class)));
        });
    }
}
