<?php
namespace ValuePad\Api\Appraiser\V2_0\Support;

use Ascope\Libraries\Validation\Rules\Enum;
use ValuePad\Core\Asc\Enums\Certification;
use ValuePad\Core\Appraiser\Persistables\CoveragePersistable;

trait LicenseConfigurationTrait
{
    /**
     * @param array $options
     * @return array
     */
    protected function getLicenseConfiguration(array $options = [])
    {
        $namespace = ConfigurationUtilities::resolveNamespaceFromOptions($options);

        return [
            $namespace . 'number' => 'string',
            $namespace . 'state' => 'string',
            $namespace . 'expiresAt' => 'datetime',
            $namespace . 'certifications' => [new Enum(Certification::class)],
            $namespace . 'isFhaApproved' => 'bool',
            $namespace . 'isCommercial' => 'bool',
            $namespace . 'document' => 'document',
            $namespace . 'coverage' => [
                'county' => 'int',
                'zips' => 'string[]'
            ]
        ];
    }

	/**
	 * @param array $options
	 * @return array
	 */
    protected function getPopulatorConfig(array $options = [])
    {
		$namespace = ConfigurationUtilities::resolveNamespaceFromOptions($options);

        return [
            'map' => [
				$namespace.'coverage' => 'coverages'
            ],
			'hint' => [
				$namespace.'coverage' => 'collection:'.CoveragePersistable::class
			]
        ];
    }

	/**
	 * @param array $options
	 * @return array
	 */
    protected function getExtractorConfig(array $options = [])
    {
        $namespace = ConfigurationUtilities::resolveNamespaceFromOptions($options);

        return [
            'map' => [
                $namespace . 'coverages' => 'coverage'
            ]
        ];
    }
}
