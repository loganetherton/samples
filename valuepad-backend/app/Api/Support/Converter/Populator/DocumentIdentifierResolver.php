<?php
namespace ValuePad\Api\Support\Converter\Populator;

use Ascope\Libraries\Converter\Populator\Resolvers\AbstractResolver;
use ReflectionParameter;
use ValuePad\Core\Document\Persistables\Identifier;

/**
 *
 *
 */
class DocumentIdentifierResolver extends AbstractResolver
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
        if (!$class = $parameter->getClass()) {
            return false;
        }

        return $class->getName() === Identifier::class || $class->isSubclassOf(Identifier::class);
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
        /**
         *
         * @var Identifier $identifier
         */
        $identifier = $parameter->getClass()->newInstance();

        return static::populate($value, $identifier);
    }

    /**
     *
     * @param int|array $value
     * @param Identifier $identifier
     * @return Identifier
     */
    public static function populate($value, Identifier $identifier)
    {
        if (! is_array($value)) {
            $value = [
                'id' => $value
            ];
        }

        $identifier->setId($value['id']);
        $identifier->setToken(array_take($value, 'token'));

        return $identifier;
    }
}
