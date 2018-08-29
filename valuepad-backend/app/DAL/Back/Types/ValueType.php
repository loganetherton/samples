<?php
namespace ValuePad\DAL\Back\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use ValuePad\DAL\Support\AbstractType;

/**
 * The reason why the value is put into the json object is that this way we don't loose type of the value.
 * For example, if type of the value is boolean it will remain boolean after restoring the value from the database.
 *
 *
 */
class ValueType extends AbstractType
{
	/**
	 * @param array $fieldDeclaration
	 * @param AbstractPlatform $platform
	 * @return string
	 */
	public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
	{
		return 'VARCHAR(255)';
	}

	/**
	 * @param mixed $value
	 * @param AbstractPlatform $platform
	 * @return mixed
	 */
	public function convertToPHPValue($value, AbstractPlatform $platform)
	{
		return json_decode($value, true)['value'];
	}

	/**
	 * @param mixed $value
	 * @param AbstractPlatform $platform
	 * @return string
	 */
	public function convertToDatabaseValue($value, AbstractPlatform $platform)
	{
		return json_encode(['value' => $value]);
	}
}
