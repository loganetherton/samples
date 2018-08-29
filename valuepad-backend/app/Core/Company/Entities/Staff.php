<?php
namespace ValuePad\Core\Company\Entities;

use ValuePad\Core\User\Entities\User;

class Staff
{
    /**
     * @var int
     */
    private $id;
    public function setId($id) { $this->id = $id; }
    public function getId() { return $this->id; }

    /**
     * @var Company $company
     */
    private $company;
    public function setCompany(Company $company) { $this->company = $company; }
    public function getCompany() { return $this->company; }

    /**
     * @var Branch
     */
    private $branch;
    public function setBranch(Branch $branch) { $this->branch = $branch; }
    public function getBranch() { return $this->branch; }

    /**
     * @var User
     */
    private $user;
    public function setUser(User $user) { $this->user = $user; }
    public function getUser() { return $this->user; }

    /**
     * @var string
     */
    private $email = null;
    public function setEmail($email) { $this->email = $email; }
    public function getEmail()
    {
        if (! $this->email) {
            return $this->getUser()->getEmail();
        }

        return $this->email;
    }

    /**
     * @var string
     */
    private $phone = null;
    public function setPhone($phone) { $this->phone = $phone; }
    public function getPhone()
    {
        if (! $this->phone) {
            return $this->getUser()->getPhone();
        }

        return $this->phone;
    }

    /**
     * @var bool
     */
    private $isManager = false;
    public function setManager($flag) { $this->isManager = $flag; }
    public function isManager() { return $this->isManager; }

    /**
     * @var bool
     */
    private $isRfpManager = false;
    public function setRfpManager($flag) { $this->isRfpManager = $flag; }
    public function isRfpManager() { return $this->isRfpManager; }

    /**
     * @var bool
     */
    private $isAdmin = false;
    public function setAdmin($flag) { $this->isAdmin = $flag; }
    public function isAdmin() { return $this->isAdmin; }
}
