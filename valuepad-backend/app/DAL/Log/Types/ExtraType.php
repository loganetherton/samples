<?php
namespace ValuePad\DAL\Log\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use ValuePad\Core\Appraisal\Enums\ProcessStatus;
use ValuePad\Core\Log\Extras\Extra;
use ValuePad\Core\Log\Extras\ExtraInterface;
use ValuePad\DAL\Support\AbstractType;
use DateTime;
use RuntimeException;

class ExtraType extends AbstractType
{
	/**
	 * @param array $fieldDeclaration
	 * @param AbstractPlatform $platform
	 * @return string
	 */
	public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
	{
		return 'TEXT';
	}

	/**
	 * @param mixed $value
	 * @param AbstractPlatform $platform
	 * @return array
	 */
	public function convertToPHPValue($value, AbstractPlatform $platform)
	{
		$data = json_decode($value, true);

		$data = array_map_recursive(function($value){

			if (!is_array($value)){
				return $value;
			}

			$type = array_take($value, '_');

			if ($type === 'datetime'){
				return new DateTime($value['value']);
			}

			if ($type === 'process-status'){
				return new ProcessStatus($value['value']);
			}

			throw new RuntimeException('Unable to resolve an instance from "'.$value['_'].'".');

		}, $data, function($value){
			return is_array($value)
				&& array_take($value, '_') !== null
				&& array_take($value, 'value') !== null
				&& count($value) == 2;
		});

		$extra = new Extra();
		$extra->setData($data);

		return $extra;
	}

	/**
	 * @param ExtraInterface $value
	 * @param AbstractPlatform $platform
	 * @return string
	 */
	public function convertToDatabaseValue($value, AbstractPlatform $platform)
	{
		$data = $value->getData();

		$data = array_map_recursive(function($value){
			if ($value instanceof  DateTime){
				return ['_' => 'datetime', 'value' => $value->format('Y-m-d H:i:s')];
			}

			if ($value instanceof  ProcessStatus){
				return ['_' => 'process-status', 'value' => (string) $value];
			}

			if (is_object($value)){
				throw new RuntimeException('Unable to process an instance of "'.get_class($value).'".');
			}

			return $value;
		}, $data);

		return json_encode($data);
	}
}
