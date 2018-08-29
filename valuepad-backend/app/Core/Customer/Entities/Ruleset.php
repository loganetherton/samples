<?php
namespace ValuePad\Core\Customer\Entities;

class Ruleset
{
    /**
     * @var int
     */
    private $id;
    public function setId($id) { $this->id = $id; }
    public function getId() { return $this->id; }

    /**
     * @var string
     */
    private $label;
    public function setLabel($label) { $this->label = $label; }
    public function getLabel() { return $this->label; }

    /**
     * @var int
     */
    private $level;
    public function setLevel($level) { $this->level = $level; }
    public function getLevel() { return $this->level; }

    /**
     * @var Rules
     */
    private $rules;
    public function giveRules(Rules $rules) { $this->rules = $rules; }
    public function takeRules() { return $this->rules; }

    /**
     * @return array
     */
    public function getRules()
    {
        $rules = $this->takeRules();

        $result = [];

        foreach ($rules->getAvailable() as $available) {
            $result[$available] = $rules->{'get'.$available}();
        }

        return $result;
    }


    /**
     * @var Customer
     */
    private $customer;
    public function setCustomer(Customer $customer) {$this->customer = $customer; }
    public function getCustomer() { return $this->customer; }
}
