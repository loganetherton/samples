<?php
namespace ValuePad\Core\Customer\Persistables;

use ValuePad\Core\Customer\Enums\Formats;
use ValuePad\Core\Customer\Enums\ExtraFormats;

class DocumentSupportedFormatsPersistable
{
    /**
     * @var Formats
     */
    private $primary;
    public function setPrimary(Formats $formats) { $this->primary = $formats; }
    public function getPrimary() { return $this->primary; }


    /**
     * @var ExtraFormats
     */
    private $extra;
    public function setExtra(ExtraFormats $formats = null) { $this->extra = $formats; }
    public function getExtra() { return $this->extra; }

    /**
     * @var int
     */
    private $jobType;
    public function setJobType($jobType) { $this->jobType = $jobType; }
    public function getJobType() { return $this->jobType; }
}
