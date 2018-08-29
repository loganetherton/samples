<?php
namespace ValuePad\Live\Handlers;
use ValuePad\Api\Appraisal\V2_0\Transformers\AdditionalDocumentTransformer;
use ValuePad\Api\Appraisal\V2_0\Transformers\OrderTransformer;
use ValuePad\Core\Appraisal\Notifications\DeleteAdditionalDocumentNotification;

class DeleteOrderAdditionalDocumentHandler extends AbstractOrderHandler
{
    /**
     * @return string
     */
    protected function getName()
    {
        return 'delete-additional-document';
    }

    /**
     * @param DeleteAdditionalDocumentNotification $notification
     * @return array
     */
    protected function getData($notification)
    {
        return [
            'order' => $this->transformer(OrderTransformer::class)->transform($notification->getOrder()),
            'additionalDocument' => $this->transformer(AdditionalDocumentTransformer::class)
                ->transform($notification->getAdditionalDocument())
        ];
    }
}
