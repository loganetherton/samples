<?php
namespace ValuePad\Api\User\V2_0\Transformers;
use ValuePad\Core\Company\Services\StaffService;
use ValuePad\Core\User\Entities\User;

class IsBossCalculatedProperty
{
    /**
     * @var StaffService
     */
    private $staffService;

    /**
     * @param StaffService $staffService
     */
    public function __construct(StaffService $staffService)
    {
        $this->staffService = $staffService;
    }

    /**
     * @param User $user
     * @return bool
     */
    public function __invoke(User $user)
    {
        return $this->staffService->isBoss($user->getId());
    }
}
