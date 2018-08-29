<?php
namespace ValuePad\Api\Appraiser\V2_0\Transformers;

use ValuePad\Api\Appraiser\V2_0\Support\LicenseConfigurationTrait;
use ValuePad\Api\Assignee\V2_0\Support\CoverageReformatter;
use ValuePad\Api\Support\BaseTransformer;
use ValuePad\Core\Appraiser\Entities\Appraiser;

class AppraiserTransformer extends BaseTransformer
{
	use LicenseConfigurationTrait;

	/**
     * @param object|Appraiser $item
     * @return array
     */
    public function transform($item)
    {
        $data = $this->extract($item, $this->getExtractorConfig([
			'namespace' => 'qualifications.primaryLicense'
		]));

		$path = 'qualifications.primaryLicense.coverage';

		if (array_has($data, $path)){
			array_set($data, $path, CoverageReformatter::reformat(array_get($data, $path)));
		}

		return $data;
    }
}
