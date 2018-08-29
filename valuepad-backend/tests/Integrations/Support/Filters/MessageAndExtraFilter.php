<?php
namespace ValuePad\Tests\Integrations\Support\Filters;

use Ascope\QA\Support\Filters\FilterInterface;

class MessageAndExtraFilter implements FilterInterface
{
    /**
     * @param array $data
     * @return array
     */
    public function filter(array $data)
    {
        $result = [];

        foreach ($data as $key => $value){
            unset($value['message'], $value['extra']);
            $result[$key] = $value;
        }

        return $result;
    }
}
