<?php
namespace ValuePad\Api\Company\V2_0\Processors;

use Ascope\Libraries\Validation\Rules\Enum;
use ValuePad\Api\Support\BaseProcessor;
use ValuePad\Core\Company\Persistables\InvitationPersistable;
use ValuePad\Core\Invitation\Enums\Requirement;

class InvitationsProcessor extends BaseProcessor
{
    /**
     * @return array
     */
    protected function configuration()
    {
        return [
            'ascAppraiser' => 'int',
            'email' => 'string',
            'phone' => 'string',
            'requirements' => [new Enum(Requirement::class)]
        ];
    }

    /**
     * @return InvitationPersistable
     */
    public function createPersistable()
    {
        return $this->populate(new InvitationPersistable());
    }
}
