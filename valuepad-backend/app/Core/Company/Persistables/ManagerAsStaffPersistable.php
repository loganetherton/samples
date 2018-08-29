<?php
namespace ValuePad\Core\Company\Persistables;

class ManagerAsStaffPersistable extends StaffPersistable
{
    /**
     * @var ManagerPersistable
     */
    private $user;
    public function setUser(ManagerPersistable $persistable) { $this->user = $persistable; }
    public function getUser() { return $this->user; }
}
