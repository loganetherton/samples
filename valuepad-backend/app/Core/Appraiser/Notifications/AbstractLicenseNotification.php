<?php
namespace ValuePad\Core\Appraiser\Notifications;
use ValuePad\Core\Appraiser\Entities\License;

abstract class AbstractLicenseNotification extends AbstractAppraiserNotification
{
    /**
     * @var License
     */
    private $license;

    public function __construct(License $license)
    {
        parent::__construct($license->getAppraiser());
        $this->license = $license;
    }

    /**
     * @return License
     */
    public function getLicense()
    {
        return $this->license;
    }
}
