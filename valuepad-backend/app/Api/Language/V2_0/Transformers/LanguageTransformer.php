<?php
namespace ValuePad\Api\Language\V2_0\Transformers;

use ValuePad\Api\Support\BaseTransformer;
use ValuePad\Core\Language\Entities\Language;

/**
 *
 *
 */
class LanguageTransformer extends BaseTransformer
{

    /**
     *
     * @param Language $language
     * @return array
     */
    public function transform($language)
    {
        return $this->extract($language);
    }
}
