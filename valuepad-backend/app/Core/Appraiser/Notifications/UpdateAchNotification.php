<?php
namespace ValuePad\Core\Appraiser\Notifications;
use ValuePad\Core\Appraiser\Entities\Ach;
use ValuePad\Core\Appraiser\Entities\Appraiser;

class UpdateAchNotification extends AbstractAppraiserNotification
{
    /**
     * @var Ach
     */
    private $ach;

    /**
     * @param Ach $ach
     * @param Appraiser $appraiser
     */
    public function __construct(Ach $ach, Appraiser $appraiser)
    {
        parent::__construct($appraiser);
        $this->ach = $ach;
    }

    /**
     * @return Ach
     */
    public function getAch()
    {
        return $this->ach;
    }
}
