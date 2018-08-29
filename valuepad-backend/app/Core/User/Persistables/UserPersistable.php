<?php
namespace ValuePad\Core\User\Persistables;
abstract class UserPersistable
{
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
}
