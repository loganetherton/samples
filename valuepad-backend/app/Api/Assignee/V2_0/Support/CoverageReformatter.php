<?php
namespace ValuePad\Api\Assignee\V2_0\Support;
use Ascope\Libraries\Validation\ErrorsThrowableCollection;

class CoverageReformatter
{
    /**
     * @param array $data
     * @return array
     */
    public static function reformat(array $data)
    {
        $result = [];

        foreach ($data as $item){
            $id = $item['county']['id'];

            if (!isset($result[$id])){
                $item['zips'] = isset($item['zip']) ? [$item['zip']] : [];
                unset($item['zip']);
                $result[$id] = $item;
            } else {
                $result[$id]['zips'][] = $item['zip'];
            }
        }

        return array_values($result);
    }

    /**
     * @param ErrorsThrowableCollection $errors
     * @return ErrorsThrowableCollection
     */
    public static function reformatErrors(ErrorsThrowableCollection $errors)
    {
        if (isset($errors['coverages'])){
            $errors['coverage'] = $errors['coverages'];
            unset($errors['coverages']);
        }

        return $errors;
    }
}
