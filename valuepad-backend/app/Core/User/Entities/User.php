<?php
namespace ValuePad\Core\User\Entities;

use DateTime;
use ValuePad\Core\User\Enums\Status;

abstract class User
{
    /**
     * @var int
     */
    private $id;
    public function setId($id) { $this->id = $id; }
    public function getId() { return $this->id; }

    /**
     * @var string
     */
    private $username;
    public function setUsername($username) { $this->username = $username; }
    public function getUsername() { return $this->username; }

    /**
     * @var string
     */
    private $password;
    public function setPassword($password) { $this->password = $password; }
    public function getPassword() { return $this->password; }

    /**
     * @var DateTime
     */
    private $createdAt;
    public function setCreatedAt(DateTime $datetime) { $this->createdAt = $datetime; }
    public function getCreatedAt() { return $this->createdAt; }

    /**
     * @var DateTime
     */
    private $updatedAt;
    public function setUpdatedAt(DateTime $datetime) { $this->updatedAt = $datetime; }
    public function getUpdatedAt() { return $this->updatedAt; }

    /**
     * @var Status
     */
    private $status;
    public function setStatus(Status $status) { $this->status = $status; }
    public function getStatus() { return $this->status; }

    /**
     * The properties below are not supposed to be here, they are supposed to be defined in the child classes,
     * but since Doctrine 2 does not allow search users based on the child properties we have to define them in the parent class
     * and provide accessors in the child classes
     */

    /**
     * @var string
     */
    protected $email;

    /**
     * @return string
     */
    abstract public function getDisplayName();

    public function __construct()
    {
        $this->setCreatedAt(new DateTime());
        $this->setUpdatedAt(new DateTime());
        $this->setStatus(new Status(Status::APPROVED));
    }
}
