<?php
namespace ValuePad\Core\Company\Persistables;

use ValuePad\Core\User\Persistables\UserPersistable;
use ValuePad\Core\Shared\Persistable\AvailabilityPersistable;

class ManagerPersistable extends UserPersistable
{
    /**
     * @var string
     */
    private $firstName;
    public function setFirstName($firstName) { $this->firstName = $firstName; }
    public function getFirstName() { return $this->firstName; }

    /**
     * @var string
     */
    private $lastName;
    public function setLastName($lastName) { $this->lastName = $lastName; }
    public function getLastName() { return $this->lastName; }

    /**
     * @var string
     */
    private $phone;
    public function setPhone($phone) { $this->phone = $phone; }
    public function getPhone() { return $this->phone; }

    /**
     * @var string
     */
    private $email;
    public function setEmail($email) { $this->email = $email; }
    public function getEmail() { return $this->email; }

    /**
     * @var AvailabilityPersistable
     */
    private $availability;
    public function setAvailability(AvailabilityPersistable $availability) { $this->availability = $availability; }
    public function getAvailability() { return $this->availability; }
}
