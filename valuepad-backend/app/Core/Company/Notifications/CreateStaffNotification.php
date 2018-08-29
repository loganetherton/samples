<?php
namespace ValuePad\Core\Company\Notifications;

use ValuePad\Core\Company\Entities\Staff;

class CreateStaffNotification
{
    const EXTRA_PASSWORD = 'password';

    /**
     * @var Staff
     */
    private $staff;

    /**
     * @var array
     */
    private $extra;

    /**
     * @param Staff $staff
     * @param array $extra
     */
    public function __construct(Staff $staff, array $extra = [])
    {
        $this->staff = $staff;
        $this->extra = $extra;
    }

    /**
     * @return Staff
     */
    public function getStaff()
    {
        return $this->staff;
    }

    /**
     * @return array
     */
    public function getExtra()
    {
        return $this->extra;
    }
}
