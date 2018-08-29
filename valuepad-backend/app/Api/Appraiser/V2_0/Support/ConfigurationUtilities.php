<?php
namespace ValuePad\Api\Appraiser\V2_0\Support;

class ConfigurationUtilities
{
	/**
	 * @param array $options
	 * @return mixed|string
	 */
	public static function resolveNamespaceFromOptions(array $options)
	{
		$namespace = array_take($options, 'namespace', '');

		if ($namespace) {
			$namespace = $namespace . '.';
		}

		return $namespace;
	}
}
