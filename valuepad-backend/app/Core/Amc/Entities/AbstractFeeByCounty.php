<?php
namespace ValuePad\Core\Amc\Entities;
use ValuePad\Core\Location\Entities\County;

abstract class AbstractFeeByCounty
{
    /**
     * @var int
     */
    protected $id;
    public function setId($id) { $this->id = $id; }
    public function getId() { return $this->id; }

    /**
     * @var County
     */
    protected $county;
    public function setCounty(County $county) { $this->county = $county; }
    public function getCounty() { return $this->county; }

    /**
     * @var float
     */
    protected $amount;
    public function setAmount($amount) { $this->amount = $amount; }
    public function getAmount() { return $this->amount; }
}
