<?php
namespace ValuePad\Core\Appraisal\Options;

class InspectionOptions
{
    /**
     * @var bool
     */
    private $bypassDatesValidation = false;
    public function setBypassDatesValidation($flag) { $this->bypassDatesValidation = $flag; }
    public function getBypassDatesValidation() { return $this->bypassDatesValidation; }
}
