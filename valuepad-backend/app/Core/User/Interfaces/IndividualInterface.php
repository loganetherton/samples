<?php
namespace ValuePad\Core\User\Interfaces;

interface IndividualInterface
{
    /**
     * @param string $name
     */
    public function setFirstName($name);

    /**
     * @return string
     */
    public function getFirstName();

    /**
     * @var string $name
     */
    public function setLastName($name);

    /**
     * @return string
     */
    public function getLastName();
}
