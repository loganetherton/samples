<?php
namespace ValuePad\Console\Cron;
use ValuePad\Core\Payment\Services\PaymentService;

class DeleteOldTransactionsCommand extends AbstractCommand
{
    /**
     * @param PaymentService $paymentService
     */
    public function fire(PaymentService $paymentService)
    {
        $paymentService->deleteOldTransactions();
    }
}
