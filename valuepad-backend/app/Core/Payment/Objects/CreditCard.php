<?php
namespace ValuePad\Core\Payment\Objects;

class CreditCard extends AbstractPaymentMethod
{
    /**
     * @var string
     */
    private $number;
    public function getNumber() { return $this->number; }
    public function setNumber($number) { $this->number = $number; }
}
