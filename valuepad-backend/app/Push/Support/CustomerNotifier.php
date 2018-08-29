<?php
namespace ValuePad\Push\Support;

use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use ValuePad\Core\Customer\Entities\Customer;
use ValuePad\Core\Session\Entities\Session;


class CustomerNotifier extends AbstractNotifier
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

		if ($session->getUser() instanceof Customer){
			return ;
		}

		if ($this->environment->isRelaxed()){
			return ;
		}

		$handlers = $this->container->make('config')->get('alert.push.handlers.customer', []);

        $this->forward($notification, $handlers, function(array $call, array $data){
            if ($data['type'] === 'order' && $data['event'] === 'create-document'){
                throw new ServiceUnavailableHttpException(null, 'Unable to send the document to the customer');
            }
        });
	}
}
