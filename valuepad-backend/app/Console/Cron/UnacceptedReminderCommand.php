<?php
namespace ValuePad\Console\Cron;
use ValuePad\Core\Appraisal\Services\ReminderService;

class UnacceptedReminderCommand extends AbstractCommand
{
    /**
     * @param ReminderService $reminderService
     */
    public function handle(ReminderService $reminderService)
    {
        $reminderService->handleAllUnacceptedOrders();
    }
}
