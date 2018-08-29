<?php
namespace ValuePad\Api\Support\Converter\Populator;

use Ascope\Libraries\Converter\Populator\Resolvers\AbstractResolver;
use ReflectionParameter;
use ValuePad\Core\Document\Persistables\Identifier;
use ValuePad\Core\Document\Persistables\Identifiers;

/**
 *
 *
 */
class DocumentIdentifiersResolver extends AbstractResolver
{

    /**
     * Checks whether the resolver can resolve a value
     *
     * @param string $field
     * @param mixed $value
     * @param ReflectionParameter $parameter
     * @return bool
     */
    public function canResolve($field, $value, ReflectionParameter $parameter)
    {
        if (! $class = $parameter->getClass()) {
            return false;
        }

        return $class->getName() === Identifiers::class || $class->isSubclassOf(Identifiers::class);
    }

    /**
     * Resolves a value
     *
     * @param string $field
     * @param mixed $value
     * @param mixed $oldValue
     * @param ReflectionParameter $parameter
     * @return mixed
     */
    public function resolve($field, $value, $oldValue, ReflectionParameter $parameter)
    {
        $identifiers = new Identifiers();

        foreach ($value as $identifier) {
            $identifiers->add(DocumentIdentifierResolver::populate($identifier, new Identifier()));
        }

        return $identifiers;
    }
}
