<?php
namespace ValuePad\Api\Support\Converter\Extractor\Filters;
use Ascope\Libraries\Converter\Extractor\Root;
use ValuePad\Core\Appraiser\Entities\Appraiser;
use ValuePad\Core\Customer\Entities\Settings;

class CustomerSettingsFilter extends AbstractFilter
{
    /**
     * @param string $key
     * @param Settings $object
     * @param Root $root
     * @return bool
     */
    public function isAllowed($key, $object, Root $root = null)
    {
        if ($key === 'canAppraiserChangeJobTypeFees' && !$this->session->getUser() instanceof Appraiser){
            return false;
        }

        if ($object->getCustomer()->getId() == $this->session->getUser()->getId()){
            return true;
        }

        if (in_array($key, ['pushUrl'])){
            return false;
        }

        return true;
    }
}
