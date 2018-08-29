<?php
namespace ValuePad\Api\Support\Converter\Extractor;
use Ascope\Libraries\Converter\Extractor\Root;

interface FilterInterface
{
    /**
     * @param string $key
     * @param object $object
     * @param Root $root
     * @return bool
     */
    public function isAllowed($key, $object, Root $root = null);
}
