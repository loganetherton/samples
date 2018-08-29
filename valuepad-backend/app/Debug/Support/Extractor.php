<?php
namespace ValuePad\Debug\Support;
use ValuePad\Core\Appraisal\Interfaces\ExtractorInterface;
use ValuePad\Core\Document\Entities\Document as Source;
use ValuePad\Core\Document\Persistables\DocumentPersistable as SourcePersistable;
use ValuePad\Core\Document\Enums\Format as SourceFormat;

class Extractor implements ExtractorInterface
{
    /**
     * @param Source $source
     * @return SourcePersistable[]
     */
    public function fromEnv(Source $source)
    {
        $result = [];

        $p1 = new SourcePersistable();

        $f1 = tmpfile();
        fwrite($f1, 'test');

        $p1->setLocation($f1);
        $p1->setSuggestedName('test.pdf');

        $result[SourceFormat::PDF] = $p1;

        $p2 = new SourcePersistable();

        $f2 = tmpfile();
        fwrite($f2, 'test');

        $p2->setLocation($f2);
        $p2->setSuggestedName('test.xml');

        $result[SourceFormat::XML] = $p2;

        return $result;
    }

    /**
     * @param Source $source
     * @return SourcePersistable
     */
    public function fromXml(Source $source)
    {
        $p = new SourcePersistable();

        $f = tmpfile();
        fwrite($f, 'test');

        $p->setLocation($f);
        $p->setSuggestedName('test.pdf');

        return $p;
    }
}
