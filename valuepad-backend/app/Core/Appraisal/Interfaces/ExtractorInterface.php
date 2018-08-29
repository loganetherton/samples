<?php
namespace  ValuePad\Core\Appraisal\Interfaces;

use ValuePad\Core\Document\Persistables\DocumentPersistable as SourcePersistable;
use ValuePad\Core\Document\Entities\Document as Source;

interface ExtractorInterface
{
    /**
     * @param Source $source
     * @return SourcePersistable[]
     */
    public function fromEnv(Source $source);

    /**
     * @param Source $source
     * @return SourcePersistable
     */
    public function fromXml(Source $source);
}
