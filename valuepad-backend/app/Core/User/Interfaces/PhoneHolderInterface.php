<?php
namespace ValuePad\Core\User\Interfaces;

interface PhoneHolderInterface
{
    /**
     * @var string $phone
     */
    public function setPhone($phone);

    /**
     * @return string
     */
    public function getPhone();
}
