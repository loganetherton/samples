<?php
namespace ValuePad\Api\Appraiser\V2_0\Transformers;

use ValuePad\Api\Appraiser\V2_0\Support\LicenseConfigurationTrait;
use ValuePad\Api\Assignee\V2_0\Support\CoverageReformatter;
use ValuePad\Api\Support\BaseTransformer;
use ValuePad\Core\Appraiser\Entities\License;

/**
 *
 *
 */
class LicenseTransformer extends BaseTransformer
{
	use LicenseConfigurationTrait;

    /**
     * @param License $license
     * @return array
     */
    public function transform($license)
    {
        $data = $this->extract($license, $this->getExtractorConfig());

		$data['coverage'] = CoverageReformatter::reformat($data['coverage']);

		return $data;
    }
}
