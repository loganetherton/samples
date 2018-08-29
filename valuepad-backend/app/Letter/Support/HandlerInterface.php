<?php
namespace ValuePad\Letter\Support;

use Illuminate\Mail\Mailer;

interface HandlerInterface
{
    /**
	 * @param Mailer $mailer
     * @param object $source
     */
    public function handle(Mailer $mailer, $source);
}
