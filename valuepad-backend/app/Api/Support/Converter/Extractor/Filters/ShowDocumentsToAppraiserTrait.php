<?php
namespace ValuePad\Api\Support\Converter\Extractor\Filters;
use ValuePad\Core\Amc\Entities\Amc;
use ValuePad\Core\Appraisal\Entities\Document;
use ValuePad\Core\Appraiser\Entities\Appraiser;
use ValuePad\Core\Company\Entities\Manager;
use ValuePad\Core\Customer\Entities\Customer;
use ValuePad\Core\Session\Entities\Session;
use ValuePad\Core\Shared\Interfaces\EnvironmentInterface;

trait ShowDocumentsToAppraiserTrait
{
    /**
     * @param Document $document
     * @param Session $session
     * @param EnvironmentInterface $environment
     * @return bool
     */
    private function canShowDocumentsToAppraiser(Document $document, Session $session, EnvironmentInterface $environment)
    {
        if ($environment->isRelaxed()){
            return true;
        }

        $actsAsAssignee = $environment->getAssigneeAsWhoActorActs() !== null;

        $user = $session->getUser();

        if ($document->getShowToAppraiser() === false
            && ($user instanceof Amc
                || $user instanceof Appraiser
                || $user instanceof Manager
                || ($user instanceof Customer && $actsAsAssignee))){

            return false;
        }

        return true;
    }
}
