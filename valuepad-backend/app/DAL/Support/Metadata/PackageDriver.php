<?php
namespace ValuePad\DAL\Support\Metadata;

use Doctrine\Common\Persistence\Mapping\ClassMetadata as ClassMetadataInterface;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriver;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use Doctrine\ORM\Mapping\ClassMetadata;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Generator;

/**
 *
 *
 */
class PackageDriver implements MappingDriver, MetadataClassesProvidableInterface
{
    /**
     * @var array
     */
    private $packages;

    /**
     * @var array
     */
    private $metadataClasses;

    /**
     * @param array $packages
     */
    public function __construct(array $packages)
    {
        $this->packages = $packages;
    }

    /**
     * @param string $className
     * @param ClassMetadataInterface|ClassMetadata $metadata
     */
    public function loadMetadataForClass($className, ClassMetadataInterface $metadata)
    {
        $provider = array_take($this->getMetadataClasses(), $className);

        if (! $provider) {
            return;
        }

        (new $provider())->define(new ClassMetadataBuilder($metadata));
    }

    /**
     *
     * @return array
     */
    public function getAllClassNames()
    {
        return array_keys($this->getMetadataClasses());
    }

    /**
     * @param string $className
     * @return bool
     */
    public function isTransient($className)
    {
        return ! isset($this->getMetadataClasses()[$className]);
    }

    /**
     * @return array
     */
    public function getMetadataClasses()
    {
        if ($this->metadataClasses === null) {

            foreach ($this->searchMetadataClasses('Entities') as $entityClass => $metadataClass) {
                $this->metadataClasses[$entityClass] = $metadataClass;
            }
        }

        return $this->metadataClasses;
    }

    /**
     * @param $target
     * @return Generator
     */
    private function searchMetadataClasses($target)
    {
        foreach ($this->packages as $package) {
            $path = app_path('Core/' . str_replace('\\', '/', $package) . '/' . $target);
            $entityNamespace = 'ValuePad\Core\\' . $package . '\\' . $target;
            $metadataNamespace = 'ValuePad\DAL\\' . $package . '\Metadata';

            if (! file_exists($path)) {
                continue;
            }

            $finder = new Finder();

            /**
             * @var SplFileInfo[] $files
             */
            $files = $finder->in($path)
                ->files()
                ->name('*.php');

            foreach ($files as $file) {
                $name = cut_string_right($file->getFilename(), '.php');

                $entityClass = $entityNamespace . '\\' . $name;
                $metadataClass = $metadataNamespace . '\\' . $name . 'Metadata';

                if (! class_exists($metadataClass)) {
                    continue;
                }

                yield $entityClass => $metadataClass;
            }
        }
    }
}
