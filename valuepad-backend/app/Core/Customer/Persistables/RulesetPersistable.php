<?php
namespace ValuePad\Core\Customer\Persistables;

class RulesetPersistable
{
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
     * @var array
     */
    private $rules;
    public function getRules() { return $this->rules; }
    public function setRules(array $rules) { $this->rules = $rules; }
}
