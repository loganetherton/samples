<?php
namespace ValuePad\Console\Cron;
use ValuePad\Support\Chance\Coordinator;

class TryAllAttemptsCommand extends AbstractCommand
{
    /**
     * @param Coordinator $coordinator
     */
    public function fire(Coordinator $coordinator)
    {
        $coordinator->tryAllAttempts();
    }
}
