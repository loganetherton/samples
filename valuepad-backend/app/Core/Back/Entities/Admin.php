<?php
namespace ValuePad\Core\Back\Entities;
use ValuePad\Core\User\Entities\User;
use ValuePad\Core\User\Interfaces\EmailHolderInterface;

class Admin extends User implements EmailHolderInterface
{
    /**
     * @var string
     */
    private $firstName;
    public function setFirstName($firstName) { $this->firstName = $firstName; }
    public function getFirstName() { return $this->firstName;}

    /**
     * @var string
     */
    private $lastName;
    public function setLastName($lastName) { $this->lastName = $lastName; }
    public function getLastName() { return $this->lastName; }


    public function setEmail($email) { $this->email = $email; }
    public function getEmail() { return $this->email; }

    /**
     * @return string
     */
    public function getDisplayName()
    {
        return $this->getFirstName().' '.$this->getLastName();
    }
}
