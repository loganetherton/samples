<?php
namespace ValuePad\Core\Amc\Emails;
use ValuePad\Core\Amc\Entities\Invoice;
use ValuePad\Core\Support\Letter\Email;

class InvoiceEmail extends Email
{
    /**
     * @var Invoice
     */
    private $invoice;

    /**
     * InvoiceEmail constructor.
     * @param Invoice $invoice
     */
    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
    }

    /**
     * @return Invoice
     */
    public function getInvoice()
    {
        return $this->invoice;
    }
}
