<?php
namespace ValuePad\Core\Customer\Persistables;
use ValuePad\Core\Customer\Enums\Criticality;

class SettingsPersistable
{
    /**
     * @var string
     */
    private $pushUrl;
    public function setPushUrl($url) { $this->pushUrl = $url; }
    public function getPushUrl() { return $this->pushUrl; }

    /**
     * @var int
     */
    private $daysPriorInspectionDate;
    public function setDaysPriorInspectionDate($days) { $this->daysPriorInspectionDate = $days; }
    public function getDaysPriorInspectionDate() { return $this->daysPriorInspectionDate; }

    /**
     * @var int
     */
    private $daysPriorEstimatedCompletionDate;
    public function setDaysPriorEstimatedCompletionDate($days) { $this->daysPriorEstimatedCompletionDate = $days; }
    public function getDaysPriorEstimatedCompletionDate() { return $this->daysPriorEstimatedCompletionDate; }

    /**
     * @var Criticality
     */
    private $preventViolationOfDateRestrictions;
    public function setPreventViolationOfDateRestrictions(Criticality $criticality) { $this->preventViolationOfDateRestrictions = $criticality; }
    public function getPreventViolationOfDateRestrictions() { return $this->preventViolationOfDateRestrictions; }


    /**
     * @var bool
     */
    private $disallowChangeJobTypeFees;
    public function getDisallowChangeJobTypeFees() { return $this->disallowChangeJobTypeFees; }
    public function setDisallowChangeJobTypeFees($flag) { $this->disallowChangeJobTypeFees = $flag; }

    /**
     * @var bool
     */
    private $showClientToAppraiser;
    public function getShowClientToAppraiser() { return $this->showClientToAppraiser; }
    public function setShowClientToAppraiser($flag) { $this->showClientToAppraiser = $flag; }

    /**
     * @var bool
     */
    private $showDocumentsToAppraiser;
    public function setShowDocumentsToAppraiser($flag) { $this->showDocumentsToAppraiser = $flag; }
    public function getShowDocumentsToAppraiser() { return $this->showDocumentsToAppraiser; }

    /**
     * @var bool
     */
    private $isSmsEnabled;
    public function setSmsEnabled($flag) { $this->isSmsEnabled = $flag; }
    public function isSmsEnabled() { return $this->isSmsEnabled; }

    /**
     * @var int
     */
    private $unacceptedReminder;
    public function setUnacceptedReminder($hours) { $this->unacceptedReminder = $hours; }
    public function getUnacceptedReminder() { return $this->unacceptedReminder; }

    /**
     * @var bool
     */
    private $removeAccountingData;
    public function getRemoveAccountingData() { return $this->removeAccountingData; }
    public function setRemoveAccountingData($flag) { $this->removeAccountingData = $flag; }
}
