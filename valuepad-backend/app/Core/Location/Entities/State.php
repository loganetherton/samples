<?php
namespace ValuePad\Core\Location\Entities;

class State
{
    /**
     * @var string
     */
    private $name;
    public function setName($name) { $this->name = $name; }
    public function getName() { return $this->name; }

    /**
     * @var string
     */
    private $code;
    public function getCode() { return $this->code; }
    public function setCode($code) { $this->code = $code; }
}
