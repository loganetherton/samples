<?php
namespace ValuePad\Api\Support\Converter\Extractor\Filters;
use Ascope\Libraries\Converter\Extractor\Root;
use ValuePad\Core\Appraisal\Entities\Document as Appraisal;

class DocumentFilter extends AbstractFilter
{
    use ShowDocumentsToAppraiserTrait;

    /**
     * @param string $key
     * @param object $object
     * @param Root $root
     * @return bool
     */
    public function isAllowed($key, $object, Root $root = null)
    {
        if ($root !== null  && $root->getObject() instanceof Appraisal && $root->getKey() === 'extra'){

            /**
             * @var Appraisal $appraisal
             */
            $appraisal = $root->getObject();

            return $this->canShowDocumentsToAppraiser($appraisal, $this->session, $this->environment)
            || in_array($key, ['format', 'id']);
        }

        return true;
    }
}
