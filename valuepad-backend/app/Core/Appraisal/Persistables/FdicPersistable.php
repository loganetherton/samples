<?php
namespace ValuePad\Core\Appraisal\Persistables;
use ValuePad\Core\Appraisal\Enums\AssetType;

class FdicPersistable
{
    /**
     * @var string
     */
    private $fin;
    public function setFin($number) { $this->fin = $number; }
    public function getFin() { return $this->fin; }

    /**
     * @var string
     */
    private $taskOrder;
    public function setTaskOrder($order) { $this->taskOrder = $order; }
    public function getTaskOrder() { return $this->taskOrder; }

    /**
     * @var int
     */
    private $line;
    public function setLine($line) { $this->line = $line; }
    public function getLine() { return $this->line; }

    /**
     * @var string
     */
    private $contractor;
    public function setContractor($contractor) { $this->contractor = $contractor; }
    public function getContractor() { return $this->contractor; }

    /**
     * @var string
     */
    private $assetNumber;
    public function setAssetNumber($number) { $this->assetNumber = $number; }
    public function getAssetNumber() { return $this->assetNumber; }

    /**
     * @var AssetType
     */
    private $assetType;
    public function setAssetType(AssetType $assetType) { $this->assetType = $assetType; }
    public function getAssetType() { return $this->assetType; }
}
