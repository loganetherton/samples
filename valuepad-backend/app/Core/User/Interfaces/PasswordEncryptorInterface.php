<?php
namespace ValuePad\Core\User\Interfaces;

/**
 *
 *
 */
interface PasswordEncryptorInterface
{
    /**
     * @param string $password
     * @return string
     */
    public function encrypt($password);

    /**
     *
     * @param string $password
     * @param string $hash
     * @return string
     */
    public function verify($password, $hash);
}
