<?php
namespace ValuePad\Api\Support\Converter\Extractor\Filters;
use Ascope\Libraries\Converter\Extractor\Root;
use ValuePad\Api\Support\Converter\Extractor\FilterInterface;

class NullFilter implements FilterInterface
{
    /**
     * @param string $key
     * @param object $object
     * @param Root $root
     * @return bool
     */
    public function isAllowed($key, $object, Root $root = null)
    {
        return true;
    }
}
