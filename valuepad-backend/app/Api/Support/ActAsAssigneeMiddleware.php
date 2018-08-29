<?php
namespace ValuePad\Api\Support;
use Illuminate\Http\Request;
use Closure;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use ValuePad\Core\Customer\Entities\Customer;
use ValuePad\Core\Customer\Services\CustomerService;
use ValuePad\Core\Session\Entities\Session;
use ValuePad\Core\Shared\Interfaces\EnvironmentInterface;

class ActAsAssigneeMiddleware
{
    /**
     * @var Session $session
     */
    private $session;

    /**
     * @var EnvironmentInterface
     */
    private $environment;

    /**
     * @var CustomerService
     */
    private $customerService;

    /**
     * @param Session $session
     * @param EnvironmentInterface $environment
     * @param CustomerService $customerService
     */
    public function __construct(Session $session, EnvironmentInterface $environment, CustomerService $customerService)
    {
        $this->session = $session;
        $this->environment = $environment;
        $this->customerService = $customerService;
    }

    /**
     * @param Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (!$this->canProceed()){
            throw new BadRequestHttpException('The current customer is not connected with the provided assignee.');
        }

        return $next($request);
    }

    /**
     * @return bool
     */
    private function canProceed()
    {
        if (!$this->session->getUser() instanceof Customer){
            return true;
        }

        $assigneeId = $this->environment->getAssigneeAsWhoActorActs();

        if (!$assigneeId){
            return true;
        }


        $customerId = $this->session->getUser()->getId();

        if ($this->customerService->isRelatedWithAppraiser($customerId, $assigneeId)
            || $this->customerService->isRelatedWithAmc($customerId, $assigneeId)){
            return true;
        }

        return false;
    }
}
