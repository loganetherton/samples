<?php
namespace ValuePad\Core\Document\Persistables;

use IteratorAggregate;
use ArrayIterator;
use Countable;

/**
 *
 *
 */
class Identifiers implements IteratorAggregate, Countable
{
    /**
     * @var Identifier[]
     */
    private $identifiers = [];

    /**
     * @param Identifier $identifier
     */
    public function add(Identifier $identifier)
    {
        $this->identifiers[] = $identifier;
    }

    /**
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->identifiers);
    }

    /**
     * @return array
     */
    public function getIds()
    {
        return array_map(function (Identifier $identifier) {
            return $identifier->getId();
        }, $this->identifiers);
    }

    /**
     * @return array
     */
    public function getTokens()
    {
        return array_map(function (Identifier $identifier) {
            return $identifier->getToken();
        }, $this->identifiers);
    }

    /**
     * @return int
     */
    public function count()
    {
        return count($this->identifiers);
    }
}
