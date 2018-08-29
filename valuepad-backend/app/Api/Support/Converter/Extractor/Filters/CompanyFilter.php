<?php
namespace ValuePad\Api\Support\Converter\Extractor\Filters;

use Ascope\Libraries\Converter\Extractor\Root;
use ValuePad\Api\Support\Converter\Extractor\Filters\AbstractFilter;
use ValuePad\Core\Company\Entities\Company;
use ValuePad\Support\Shortcut;

class CompanyFilter extends AbstractFilter
{
    /**
     * @param string $key
     * @param Company $object
     * @param Root $root
     * @return bool
     */
    public function isAllowed($key, $object, Root $root = null)
    {
        if (in_array($key, ['id', 'name'])) {
            return true;
        }

        if (mb_stripos($this->getRoute(), Shortcut::prependGlobalRoutePrefix('v2.0/companies/tax-id')) !== false) {
            return false;
        }

        return true;
    }
}
