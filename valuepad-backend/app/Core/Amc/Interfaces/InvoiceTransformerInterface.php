<?php
namespace ValuePad\Core\Amc\Interfaces;
use ValuePad\Core\Amc\Entities\Invoice;
use ValuePad\Core\Document\Persistables\DocumentPersistable;

interface InvoiceTransformerInterface
{
    /**
     * @param Invoice $invoice
     * @return DocumentPersistable
     */
    public function toPdf(Invoice $invoice);
}
