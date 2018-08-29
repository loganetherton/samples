<?php
namespace ValuePad\Push\Support;
use ValuePad\Core\Appraisal\Notifications\CreateOrderNotification;
use ValuePad\Core\Customer\Entities\Customer;
use ValuePad\Core\Session\Entities\Session;
use ValuePad\Support\AfterPartyMiddleware;

class AmcNotifier extends AbstractNotifier
{
    /**
     * @param object $notification
     */
    public function notify($notification)
    {
        /**
         * @var Session $session
         */
        $session = $this->container->make(Session::class);

        if (!$session->getUser() instanceof Customer){
            return ;
        }

        $handlers = $this->container->make('config')->get('alert.push.handlers.amc', []);

        if ($notification instanceof CreateOrderNotification && app()->environment() !== 'tests'){

            $this->container->make(AfterPartyMiddleware::class)->schedule(function() use ($notification, $handlers){
                sleep(1);
                $this->forward($notification, $handlers);
            });
        } else {
            $this->forward($notification, $handlers);
        }
    }
}
