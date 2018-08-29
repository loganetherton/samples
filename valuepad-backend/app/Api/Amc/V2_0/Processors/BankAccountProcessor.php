<?php
namespace ValuePad\Api\Amc\V2_0\Processors;
use Ascope\Libraries\Validation\Rules\Enum;
use ValuePad\Api\Support\BaseProcessor;
use ValuePad\Core\Payment\Enums\AccountType;
use ValuePad\Core\Payment\Objects\BankAccountRequisites;

class BankAccountProcessor extends BaseProcessor
{

    protected function configuration()
    {
        return [
            'accountType' => new Enum(AccountType::class),
            'routingNumber' => 'string',
            'accountNumber' => 'string',
            'nameOnAccount' => 'string',
            'bankName' => 'string',
            'address' => 'string',
            'city' => 'string',
            'state' => 'string',
            'zip' => 'string'
        ];
    }

    /**
     * @return BankAccountRequisites
     */
    public function createRequisites()
    {
        return $this->populate(new BankAccountRequisites());
    }
}
