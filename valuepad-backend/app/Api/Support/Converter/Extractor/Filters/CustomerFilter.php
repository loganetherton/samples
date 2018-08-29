<?php
namespace ValuePad\Api\Support\Converter\Extractor\Filters;
use Ascope\Libraries\Converter\Extractor\Root;
use ValuePad\Core\Customer\Entities\Customer;
use ValuePad\Support\Shortcut;

class CustomerFilter extends AbstractFilter
{
    /**
     * @param string $key
     * @param Customer $object
     * @param Root $root
     * @return bool
     */
    public function isAllowed($key, $object, Root $root = null)
    {
        if (in_array($this->getRoute(), [
                Shortcut::prependGlobalRoutePrefix('v2.0/sessions'),
                Shortcut::prependGlobalRoutePrefix('v2.0/customers')
            ]) && $this->isPost()){
            return true;
        }

        if ($object->getId() === object_take($this->session, 'user.id')){
            return true;
        }

        if (in_array($key, ['secret1', 'secret2'])){
            return false;
        }

        return true;
    }
}
