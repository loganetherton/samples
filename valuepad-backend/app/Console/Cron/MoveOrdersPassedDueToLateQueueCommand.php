<?php
namespace ValuePad\Console\Cron;
use ValuePad\Core\Appraisal\Services\OrderService;

class MoveOrdersPassedDueToLateQueueCommand extends AbstractCommand
{
    /**
     * @param OrderService $orderService
     */
    public function fire(OrderService $orderService)
    {
        $this->startSystemSession();

        $orderService->moveAllPassedDueToLateQueue();

        $this->endSystemSession();
    }
}
