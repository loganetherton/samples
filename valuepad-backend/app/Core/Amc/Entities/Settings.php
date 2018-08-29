<?php
namespace ValuePad\Core\Amc\Entities;

class Settings
{
    /**
     * @var int
     */
    private $id;
    public function setId($id) { $this->id = $id; }
    public function getId() { return $this->id; }

    /**
     * @var Amc
     */
    private $amc;
    public function getAmc() { return $this->amc; }

    /**
     * @param Amc $amc
     */
    public function setAmc(Amc $amc)
    {
        $this->amc = $amc;
    }

    /**
     * @var string
     */
    private $pushUrl;
    public function setPushUrl($url) { $this->pushUrl = $url; }
    public function getPushUrl() { return $this->pushUrl; }
}
