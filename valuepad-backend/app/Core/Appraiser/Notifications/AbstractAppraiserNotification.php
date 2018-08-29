<?php
namespace ValuePad\Core\Appraiser\Notifications;
use ValuePad\Core\Appraiser\Entities\Appraiser;

abstract class AbstractAppraiserNotification
{
    /**
     * @var Appraiser
     */
    private $appraiser;

    /**
     * @param Appraiser $appraiser
     */
    public function __construct(Appraiser $appraiser)
    {
        $this->appraiser = $appraiser;
    }

    /**
     * @return Appraiser
     */
    public function getAppraiser()
    {
        return $this->appraiser;
    }
}
