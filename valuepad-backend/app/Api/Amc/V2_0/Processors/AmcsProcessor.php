<?php
namespace ValuePad\Api\Amc\V2_0\Processors;
use Ascope\Libraries\Validation\Rules\Enum;
use ValuePad\Api\Support\BaseProcessor;
use ValuePad\Core\Shared\Interfaces\EnvironmentInterface;
use ValuePad\Core\User\Enums\Status;
use ValuePad\Core\Amc\Persistables\AmcPersistable;

class AmcsProcessor extends BaseProcessor
{
    public function configuration()
    {
        $data = [
            'username' => 'string',
            'password' => 'string',
            'email' => 'string',
            'companyName' => 'string',
            'address1' => 'string',
            'address2' => 'string',
            'city' => 'string',
            'state' => 'string',
            'zip' => 'string',
            'phone' => 'string',
            'fax' => 'string',
            'lenders' => 'string'
        ];

        /**
         * @var EnvironmentInterface $environment
         */
        $environment = $this->container->make(EnvironmentInterface::class);

        if ($this->isAdmin() || $environment->isRelaxed()){
            $data['status'] = new Enum(Status::class);
        }

        return $data;
    }

    /**
     * @return AmcPersistable
     */
    public function createPersistable()
    {
        return $this->populate(new AmcPersistable());
    }
}
