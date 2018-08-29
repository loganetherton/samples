<?php
namespace ValuePad\Core\Amc\Notifications;
use ValuePad\Core\Amc\Entities\Amc;

abstract class AbstractNotification
{
    /**
     * @var Amc
     */
    private $amc;

    /**
     * @param Amc $amc
     */
    public function __construct(Amc $amc)
    {
        $this->amc = $amc;
    }

    /**
     * @return Amc
     */
    public function getAmc()
    {
        return $this->amc;
    }
}
