<?php
namespace ValuePad\Api\Appraisal\V2_0\Support;

use ValuePad\Api\Appraiser\V2_0\Support\ConfigurationUtilities;

trait AdditionalDocumentsConfigurationTrait
{
	/**
	 * @param array $options
	 * @return array
	 */
	protected function getAdditionalDocumentsConfiguration(array $options = [])
	{
		$namespace = ConfigurationUtilities::resolveNamespaceFromOptions($options);

		return [
			$namespace.'type' => 'int',
			$namespace.'label' => 'string',
			$namespace.'document' => 'document'
		];
	}
}
