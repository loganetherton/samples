<?php
namespace ValuePad\Console\Cron;
use ValuePad\Core\Amc\Services\InvoiceService;

class GenerateAmcMonthlyInvoicesCommand extends AbstractCommand
{
    /**
     * @param InvoiceService $invoiceService
     */
    public function fire(InvoiceService $invoiceService)
    {
        $invoiceService->generateMonthlyInvoices();
    }
}
