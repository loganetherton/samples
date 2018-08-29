<?php
namespace ValuePad\Api\Amc\V2_0\Processors;
use Ascope\Libraries\Processor\AbstractProcessor;
use Ascope\Libraries\Validation\Binder;
use Ascope\Libraries\Validation\Property;
use Ascope\Libraries\Validation\Rules\Enum;
use Ascope\Libraries\Validation\Rules\Obligate;
use ValuePad\Core\Payment\Enums\Means;

class PayInvoiceProcessor extends AbstractProcessor
{
    protected function rules(Binder $binder)
    {
        $binder->bind('means', function(Property $property){
            $property
                ->addRule(new Obligate())
                ->addRule(new Enum(Means::class));

        });
    }

    /**
     * @return array
     */
    protected function allowable()
    {
        return [
            'means'
        ];
    }

    /**
     * @return Means
     */
    public function getMeans()
    {
        return new Means($this->get('means'));
    }
}
