<?php
namespace ValuePad\Console\Cron;
use ValuePad\Core\Appraisal\Services\OrderService;

class FixPropertiesCoordinatesCommand extends AbstractCommand
{
    /**
     * @param OrderService $orderService
     */
    public function fire(OrderService $orderService)
    {
        $orderService->fixPropertiesCoordinates();
    }
}
