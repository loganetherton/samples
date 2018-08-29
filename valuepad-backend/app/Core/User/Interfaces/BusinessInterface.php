<?php
namespace ValuePad\Core\User\Interfaces;

interface BusinessInterface
{
    /**
     * @var string $name
     */
    public function setCompanyName($name);

    /**
     * @return string
     */
    public function getCompanyName();
}
