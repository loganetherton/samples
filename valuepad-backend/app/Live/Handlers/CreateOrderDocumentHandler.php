<?php
namespace ValuePad\Live\Handlers;
use ValuePad\Api\Appraisal\V2_0\Transformers\DocumentTransformer;
use ValuePad\Api\Appraisal\V2_0\Transformers\OrderTransformer;
use ValuePad\Api\Support\Converter\Extractor\Filters\AbstractAppraisalFilter;
use ValuePad\Core\Appraisal\Notifications\CreateDocumentNotification;

class CreateOrderDocumentHandler extends AbstractOrderHandler
{
    /**
     * @return string
     */
    protected function getName()
    {
        return 'create-document';
    }

    /**
     * @param CreateDocumentNotification $notification
     * @return array
     */
    protected function getData($notification)
    {
        return [
            'order' => $this->transformer(OrderTransformer::class)->transform($notification->getOrder()),
            'document' => $this->transformer(DocumentTransformer::class)
                ->transform($notification->getDocument())
        ];
    }
}
