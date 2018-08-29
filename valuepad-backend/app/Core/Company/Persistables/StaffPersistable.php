<?php
namespace ValuePad\Core\Company\Persistables;

class StaffPersistable
{
    /**
     * @var int
     */
    private $branch;
    public function setBranch($branch) { $this->branch = $branch; }
    public function getBranch() { return $this->branch; }

    /**
     * @var string
     */
    private $email;
    public function setEmail($email) { $this->email = $email; }
    public function getEmail() { return $this->email; }

    /**
     * @var string
     */
    private $phone;
    public function setPhone($phone) { $this->phone = $phone; }
    public function getPhone() { return $this->phone; }

    /**
     * @var bool
     */
    private $isManager;
    public function setManager($flag) { $this->isManager = $flag; }
    public function isManager() { return $this->isManager; }

    /**
     * @var bool
     */
    private $isRfpManager;
    public function setRfpManager($flag) { $this->isRfpManager = $flag; }
    public function isRfpManager() { return $this->isRfpManager; }

    /**
     * @var bool
     */
    private $isAdmin;
    public function setAdmin($flag) { $this->isAdmin = $flag; }
    public function isAdmin() { return $this->isAdmin; }
}
