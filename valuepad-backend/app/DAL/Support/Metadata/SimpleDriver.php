<?php
namespace ValuePad\DAL\Support\Metadata;

use Doctrine\Common\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use Doctrine\Common\Persistence\Mapping\ClassMetadata as ClassMetadataInterface;
use Doctrine\ORM\Mapping\ClassMetadata;

class SimpleDriver implements MappingDriver, MetadataClassesProvidableInterface
{
	/**
	 * @var array
	 */
	private $source;

	/**
	 * @param array $source
	 */
	public function __construct(array $source)
	{
		$this->source = $source;
	}

	/**
	 * Loads the metadata for the specified class into the provided container.
	 *
	 * @param string $className
	 * @param ClassMetadataInterface|ClassMetadata $metadata
	 *
	 * @return void
	 */
	public function loadMetadataForClass($className, ClassMetadataInterface $metadata)
	{
		$provider = array_take($this->source, $className);

		if (!$provider) {
			return;
		}

		(new $provider())->define(new ClassMetadataBuilder($metadata));
	}

	/**
	 * Gets the names of all mapped classes known to this driver.
	 *
	 * @return array The names of all mapped classes known to this driver.
	 */
	public function getAllClassNames()
	{
		return array_keys($this->source);
	}

	/**
	 * Returns whether the class with the specified name should have its metadata loaded.
	 * This is only the case if it is either mapped as an Entity or a MappedSuperclass.
	 *
	 * @param string $className
	 *
	 * @return boolean
	 */
	public function isTransient($className)
	{
		return ! isset($this->source[$className]);
	}

	/**
	 * @return array
	 */
	public function getMetadataClasses()
	{
		return $this->source;
	}
}
