<?php
namespace ValuePad\Api\Company\V2_0\Processors;

use ValuePad\Api\Support\BaseProcessor;
use ValuePad\Core\Company\Persistables\ManagerPersistable;
use ValuePad\Api\Shared\Support\AvailabilityConfigurationTrait;

class ManagersProcessor extends BaseProcessor
{
    use AvailabilityConfigurationTrait;
    /**
     * @return array
     */
    protected function configuration()
    {
        return self::getPayloadSpecification();
    }


    public static function getPayloadSpecification()
    {
        $namespace = 'availability';
        return array_merge([
            $namespace . 'isOnVacation' => 'bool',
            $namespace . 'from' => 'datetime',
            $namespace . 'to' => 'datetime',
            $namespace . 'message' => 'string'
        ], [
            'username' => 'string',
            'password' => 'string',
            'firstName' => 'string',
            'lastName' => 'string',
            'phone' => 'string',
            'email' => 'string'
        ]);
    }

    /**
     * @return ManagerPersistable
     */
    public function createPersistable()
    {
        return $this->populate(new ManagerPersistable());
    }
}
