<?php
namespace ValuePad\Api\Back\V2_0\Processors;
use ValuePad\Api\Support\BaseProcessor;
use ValuePad\Core\Back\Persistables\AdminPersistable;

class AdminsProcessor extends BaseProcessor
{
    protected function configuration()
    {
        return [
            'username' => 'string',
            'password' => 'string',
            'firstName' => 'string',
            'lastName' => 'string',
            'email' => 'string'
        ];
    }

    /**
     * @return AdminPersistable
     */
    public function createPersistable()
    {
        return $this->populate(new AdminPersistable());
    }
}
