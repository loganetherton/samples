<?php
namespace ValuePad\Core\Appraisal\Enums\Property;

use Ascope\Libraries\Enum\Enum;

class ContactType extends Enum
{
    const BORROWER = 'borrower';
    const CO_BORROWER = 'co-borrower';
    const OWNER = 'owner';
    const REALTOR = 'realtor';
    const OTHER = 'other';
    const ASSISTANT = 'assistant';
    const LISTING_AGENT = 'listing-agent';
    const SELLING_AGENT = 'selling-agent';
}
