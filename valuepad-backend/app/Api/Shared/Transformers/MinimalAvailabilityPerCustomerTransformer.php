<?php
namespace ValuePad\Api\Shared\Transformers;

use ValuePad\Api\Support\BaseTransformer;

class MinimalAvailabilityPerCustomerTransformer extends BaseTransformer
{
    /**
     * Strips out certain attributes to make the object identical to Availability
     *
     * @param AvailabilityPerCustomer $availabilityPerCustomer
     * @return array
     */
    public function transform($availabilityPerCustomer)
    {
        return $this->extract($availabilityPerCustomer, [
            'ignore' => ['id', 'customer', 'user']
        ]);
    }
}
