<?php
namespace ValuePad\Api\Document\V2_0\Processors;

use Ascope\Libraries\Processor\AbstractProcessor;
use Ascope\Libraries\Validation\Binder;
use Ascope\Libraries\Validation\Property;
use ValuePad\Api\Support\Converter\Populator\DocumentPersistableResolver;
use ValuePad\Api\Support\Validation\Rules\Document;
use ValuePad\Core\Document\Persistables\DocumentPersistable;

/**
 *
 *
 */
class DocumentsProcessor extends AbstractProcessor
{
    /**
     * @param Binder $binder
     */
    protected function rules(Binder $binder)
    {
        $binder->bind('document', function (Property $property) {
            $property->addRule(new Document());
        });
    }

    /**
     *
     * @return DocumentPersistable
     */
    public function createPersistable()
    {
        $file = $this->get('document');

        $persistable = new DocumentPersistable();

        if ($file) {
            DocumentPersistableResolver::populate($persistable, $file);
        }

        return $persistable;
    }
}
