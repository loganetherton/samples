<?php
namespace ValuePad\DAL\Support;

use Doctrine\DBAL\Types\Type;

abstract class AbstractType extends Type
{
    /**
     * @return string
     */
    public function getName()
    {
        return get_called_class();
    }
}
