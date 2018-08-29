<?php
namespace ValuePad\Api\Customer\V2_0\Processors;
use ValuePad\Api\Support\BaseProcessor;
use ValuePad\Core\Customer\Persistables\ClientPersistable;

class ClientsProcessor extends BaseProcessor
{
    protected function configuration()
    {
        return [
            'name' => 'string',
            'address1' => 'string',
            'address2' => 'string',
            'zip' => 'string',
            'city' => 'string',
            'state' => 'string'
        ];
    }

    /**
     * @return ClientPersistable
     */
    public function createPersistable()
    {
        return $this->populate(new ClientPersistable());
    }
}
