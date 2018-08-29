<?php
namespace ValuePad\Api\Company\V2_0\Processors;
use ValuePad\Api\Support\BaseProcessor;
use ValuePad\Core\Company\Persistables\StaffPersistable;

class StaffProcessor extends BaseProcessor
{
    /**
     * @return array
     */
    public function configuration()
    {
        return self::getPayloadSpecification();
    }

    /**
     * @return array
     */
    public static function getPayloadSpecification()
    {
        return [
            'isAdmin' => 'bool',
            'isManager' => 'bool',
            'isRfpManager' => 'bool',
            'branch' => 'int',
            'email' => 'string',
            'phone' => 'string'
        ];
    }

    /**
     * @return StaffPersistable
     */
    public function createPersistable()
    {
        return $this->populate(new StaffPersistable());
    }
}
