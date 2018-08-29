<?php
namespace ValuePad\Core\User\Entities;
use ValuePad\Core\User\Interfaces\EmailHolderInterface;

class System extends User implements EmailHolderInterface
{
    /**
     * @var string
     */
    private $name;
    public function setName($name) { $this->name = $name; }
    public function getName() { return $this->name; }

    public function setEmail($email) { $this->email = $email; }
    public function getEmail() { return $this->email; }

    public function getDisplayName() { return $this->getName(); }
}
