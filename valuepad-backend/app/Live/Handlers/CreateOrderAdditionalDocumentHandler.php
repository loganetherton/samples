<?php
namespace ValuePad\Live\Handlers;
use ValuePad\Api\Appraisal\V2_0\Transformers\AdditionalDocumentTransformer;
use ValuePad\Api\Appraisal\V2_0\Transformers\OrderTransformer;
use ValuePad\Core\Appraisal\Notifications\CreateAdditionalDocumentNotification;

class CreateOrderAdditionalDocumentHandler extends AbstractOrderHandler
{
    /**
     * @return string
     */
    protected function getName()
    {
        return 'create-additional-document';
    }

    /**
     * @param CreateAdditionalDocumentNotification $notification
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
