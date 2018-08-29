<?php
namespace ValuePad\Api\Customer\V2_0\Processors;
use ValuePad\Api\Support\BaseProcessor;
use Ascope\Libraries\Validation\Rules\Callback;
use ValuePad\Core\Customer\Persistables\RulesetPersistable;

class RulesetsProcessor extends BaseProcessor
{
    protected function configuration()
    {
        return [
            'level' => 'int',
            'label' => 'string',
            'rules' => (new Callback(function($value){
                if (!is_array($value)){
                    return false;
                }

                foreach (array_keys($value) as $key){
                    if (!is_string($key)){
                        return false;
                    }
                }

                return true;
            }))
                ->setIdentifier('cast')
                ->setMessage('The rules must be an array of key/value pairs')
        ];
    }

    /**
     * @return RulesetPersistable
     */
    public function createPersistable()
    {
        return $this->populate(new RulesetPersistable());
    }
}
