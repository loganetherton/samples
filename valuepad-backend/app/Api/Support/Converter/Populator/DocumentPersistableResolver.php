<?php
namespace ValuePad\Api\Support\Converter\Populator;

use ValuePad\Core\Document\Persistables\DocumentPersistable;
use Ascope\Libraries\Converter\Populator\Resolvers\AbstractResolver;
use ReflectionParameter;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 *
 * @author Sergei Melnikov <me@rnr.name>
 */
class DocumentPersistableResolver extends AbstractResolver
{
    /**
     * @param string $field
     * @param mixed $value
     * @param ReflectionParameter $parameter
     * @return bool
     */
    public function canResolve($field, $value, ReflectionParameter $parameter)
    {
        $class = $parameter->getClass();

        return $class && (($class->getName() === DocumentPersistable::class)
			|| $class->isSubclassOf(DocumentPersistable::class)) && ($value instanceof UploadedFile);
    }

    /**
     *
     * @param string $field
     * @param UploadedFile $file
     * @param mixed $oldValue
     * @param ReflectionParameter $parameter
     * @return DocumentPersistable
     */
    public function resolve($field, $file, $oldValue, ReflectionParameter $parameter)
    {
        $class = $parameter->getClass();

        /**
         * @var DocumentPersistable $persistable
         */
        $persistable = $class->newInstance();

        return static::populate($persistable, $file);
    }

    /**
     *
     * @param DocumentPersistable $persistable
     * @param UploadedFile $file
     * @return DocumentPersistable
     */
    public static function populate(DocumentPersistable $persistable, UploadedFile $file)
    {
        $persistable->setLocation($file->getPathname());
        $persistable->setSuggestedName($file->getClientOriginalName());
        return $persistable;
    }
}