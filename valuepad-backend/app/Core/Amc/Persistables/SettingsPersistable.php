<?php
namespace ValuePad\Core\Amc\Persistables;

class SettingsPersistable
{
    /**
     * @var string
     */
    private $pushUrl;
    public function setPushUrl($url) { $this->pushUrl = $url; }
    public function getPushUrl() { return $this->pushUrl; }
}
