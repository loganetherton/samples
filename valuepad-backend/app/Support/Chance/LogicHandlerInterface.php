<?php
namespace ValuePad\Support\Chance;

interface LogicHandlerInterface
{
    /**
     * @param Attempt $attempt
     * @return bool
     */
    public function handle(Attempt $attempt);

    /**
     * @param Attempt $attempt
     */
    public function outOfAttempts(Attempt $attempt);
}
