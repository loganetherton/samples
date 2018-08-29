<?php
namespace ValuePad\Api\Company\V2_0\Transformers;

use ValuePad\Api\Support\BaseTransformer;
use ValuePad\Core\Company\Entities\Company;

class CompanyTransformer extends BaseTransformer
{
    /**
     * @param Company $company
     * @return array
     */
    public function transform($company)
    {
        return $this->extract($company);
    }
}
