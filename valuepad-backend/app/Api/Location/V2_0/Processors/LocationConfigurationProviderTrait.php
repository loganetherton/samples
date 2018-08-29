<?php
namespace ValuePad\Api\Location\V2_0\Processors;

/**
 *
 *
 */
trait LocationConfigurationProviderTrait
{
    public function getLocationConfiguration(array $options = [])
    {
		$result = [];

		$fields = ['address1', 'address2', 'state', 'city', 'zip'];

		foreach ($fields as $field){
			$result = $this->prepareField($field, $options, $result);
		}

		return $result;
    }

	/**
	 * @param string $field
	 * @param array $options
	 * @param array
	 * @return array
	 */
	private function prepareField($field, array $options, array $result)
	{
		if (in_array($field, array_take($options, 'ignore', []))){
			return $result;
		}

		$map = array_take($options, 'rename', []);

		$field = array_take($map, $field, $field);

		$prefix = array_take($options, 'prefix');

		if ($prefix){
			$field = $prefix.(ends_with($prefix, '.') ? $field : ucfirst($field));
		}

		$result[$field] = 'string';

		return $result;
	}
}
