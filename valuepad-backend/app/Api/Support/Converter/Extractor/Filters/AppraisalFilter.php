<?php
namespace ValuePad\Api\Support\Converter\Extractor\Filters;
use Ascope\Libraries\Converter\Extractor\Root;
use ValuePad\Core\Appraisal\Entities\Document as Appraisal;

class AppraisalFilter extends AbstractFilter
{
    use ShowDocumentsToAppraiserTrait;

    /**
     * @param string $key
     * @param Appraisal $object
     * @param Root $root
     * @return bool
     */
    public function isAllowed($key, $object, Root $root = null)
    {
        return $this->canShowDocumentsToAppraiser($object, $this->session, $this->environment)
            || in_array($key, ['showToAppraiser', 'extra', 'id']);
    }
}
