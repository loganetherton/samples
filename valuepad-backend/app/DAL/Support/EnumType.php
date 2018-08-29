<?php
namespace ValuePad\DAL\Support;

use Ascope\Libraries\Enum\Enum;
use Doctrine\DBAL\Platforms\AbstractPlatform;

abstract class EnumType extends AbstractType
{
    /**
     * @return string
     */
    abstract protected function getEnumClass();

	/**
	 * @return string
	 */
	protected function getEnumCollectionClass()
	{
		return null;
	}

	/**
	 * @return bool
	 */
	protected function isArray()
	{
		return $this->getEnumCollectionClass() !== null;
	}

    /**
     * @param array $fieldDeclaration
     * @param AbstractPlatform $platform
     * @return string
     */
    public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
		if ($this->isArray()){
			return 'VARCHAR(255)';
		}

        return 'VARCHAR(100)';
    }

    /**
     * @param mixed $value
     * @param AbstractPlatform $platform
     * @return Enum
     */
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if ($value === null) {
            return null;
        }

		if ($this->isArray()){
			$result = json_decode($value);

			$class = $this->getEnumCollectionClass();

			$collection = new $class();

			foreach ($result as $value){
				$collection->push($this->createEnum($value));
			}

			return $collection;
		}

		return $this->createEnum($value);
    }

	/**
	 * @param string $value
	 * @return Enum
	 */
	private function createEnum($value)
	{
		$class = $this->getEnumClass();
		return new $class($value);
	}

    /**
     * @param Enum $value
     * @param AbstractPlatform $platform
     * @return string
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if ($value === null) {
            return null;
        }

		if ($this->isArray()){
			$result = [];

			$values = $value;

			foreach ($values as $value){
				$result[] = $value->value();
			}

			return json_encode($result);
		}

		return $value->value();
    }
}
