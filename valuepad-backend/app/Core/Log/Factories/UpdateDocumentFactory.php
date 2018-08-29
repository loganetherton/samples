<?php
namespace ValuePad\Core\Log\Factories;
use ValuePad\Core\Appraisal\Notifications\UpdateDocumentNotification;
use ValuePad\Core\Document\Entities\Document;
use ValuePad\Core\Log\Enums\Action;

class UpdateDocumentFactory extends AbstractDocumentFactory
{
    /**
     * @param UpdateDocumentNotification $notification
     * @return Document
     */
    protected function getDocument($notification)
    {
        return $notification->getDocument()->getPrimary();
    }

    /**
     * @return Action
     */
    protected function getAction()
    {
        return new Action(Action::UPDATE_DOCUMENT);
    }
}
