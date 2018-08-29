<?php
namespace ValuePad\Api\Amc\V2_0\Transformers;
use ValuePad\Api\Assignee\V2_0\Support\CoverageReformatter;
use ValuePad\Api\Support\BaseTransformer;

class LicenseTransformer extends BaseTransformer
{
    /**
     * @param object $item
     * @return array
     */
    public function transform($item)
    {
        $data = $this->extract($item, [
            'map' => [
                'coverages' => 'coverage'
            ]
        ]);

        $data['coverage'] = CoverageReformatter::reformat($data['coverage']);

        return $data;
    }
}
