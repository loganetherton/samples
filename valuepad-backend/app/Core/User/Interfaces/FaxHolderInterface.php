<?php
namespace ValuePad\Core\User\Interfaces;

interface FaxHolderInterface
{
    /**
     * @var string $fax
     */
    public function setFax($fax);

    /**
     * @return string
     */
    public function getFax();
}
