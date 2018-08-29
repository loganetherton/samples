<?php
namespace ValuePad\Live\Handlers;
use ValuePad\Api\Appraisal\V2_0\Transformers\DocumentTransformer;
use ValuePad\Api\Appraisal\V2_0\Transformers\OrderTransformer;
use ValuePad\Core\Appraisal\Notifications\DeleteDocumentNotification;

class DeleteOrderDocumentHandler extends AbstractOrderHandler
{
    /**
     * @return string
     */
    protected function getName()
    {
        return 'delete-document';
    }

    /**
     * @param DeleteDocumentNotification $notification
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
