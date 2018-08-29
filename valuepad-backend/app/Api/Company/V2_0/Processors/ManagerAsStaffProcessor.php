<?php
namespace ValuePad\Api\Company\V2_0\Processors;
use ValuePad\Api\Support\BaseProcessor;
use ValuePad\Core\Company\Persistables\ManagerAsStaffPersistable;

class ManagerAsStaffProcessor extends BaseProcessor
{
    /**
     * @return array
     */
    protected function configuration()
    {
        $data = StaffProcessor::getPayloadSpecification();

        $data['user'] = 'array';

        foreach (ManagersProcessor::getPayloadSpecification() as $field => $rule){
            $data['user.'.$field] = $rule;
        }

        $data['notifyUser'] = 'bool';

        return $data;
    }

    /**
     * @return bool
     */
    public function notifyUser()
    {
        return $this->get('notifyUser', false);
    }

    /**
     * @return ManagerAsStaffPersistable
     */
    public function createPersistable()
    {
        return $this->populate(new ManagerAsStaffPersistable());
    }
}
