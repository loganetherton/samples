<?php
namespace ValuePad\Core\Amc\Entities;
use ValuePad\Core\Location\Entities\State;

abstract class AbstractFeeByState
{
    /**
     * @var int
     */
    protected $id;
    public function setId($id) { $this->id = $id; }
    public function getId() { return $this->id; }

    /**
     * @var State
     */
    protected $state;
    public function setState(State $state) { $this->state = $state; }
    public function getState() { return $this->state; }

    /**
     * @var float
     */
    protected $amount;
    public function setAmount($amount) { $this->amount = $amount; }
    public function getAmount() { return $this->amount; }
}
