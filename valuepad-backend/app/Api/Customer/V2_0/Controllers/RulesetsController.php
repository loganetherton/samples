<?php
namespace ValuePad\Api\Customer\V2_0\Controllers;
use Illuminate\Http\Response;
use ValuePad\Api\Customer\V2_0\Processors\RulesetsProcessor;
use ValuePad\Api\Customer\V2_0\Transformers\RulesetTransformer;
use ValuePad\Api\Support\BaseController;
use ValuePad\Core\Customer\Services\CustomerService;
use ValuePad\Core\Customer\Services\RulesetService;

class RulesetsController extends BaseController
{
    /**
     * @var RulesetService
     */
    private $rulesetService;

    /**
     * @param RulesetService $rulesetService
     */
    public function initialize(RulesetService $rulesetService)
    {
        $this->rulesetService = $rulesetService;
    }

    /**
     * @param int $customerId
     * @param RulesetsProcessor $processor
     * @return Response
     */
    public function store($customerId, RulesetsProcessor $processor)
    {
        return $this->resource->make(
            $this->rulesetService->create($customerId, $processor->createPersistable()),
            $this->transformer(RulesetTransformer::class)
        );
    }

    /**
     * @param int $customerId
     * @param int $rulesetId
     * @return Response
     */
    public function show($customerId, $rulesetId)
    {
        return $this->resource->make(
            $this->rulesetService->get($rulesetId),
            $this->transformer(RulesetTransformer::class)
        );
    }

    /**
     * @param int $customerId
     * @param int $rulesetId
     * @param RulesetsProcessor $processor
     * @return Response
     */
    public function update($customerId, $rulesetId, RulesetsProcessor $processor)
    {
        $this->rulesetService->update($rulesetId, $processor->createPersistable());

        return $this->resource->blank();
    }

    /**
     * @param int $customerId
     * @param int $rulesetId
     * @return Response
     */
    public function destroy($customerId, $rulesetId)
    {
        $this->rulesetService->delete($rulesetId);

        return $this->resource->blank();
    }

    /**
     * @param CustomerService $customerService
     * @param int $customerId
     * @param int $rulesetId
     * @return bool
     */
    public static function verifyAction(CustomerService $customerService, $customerId, $rulesetId = null)
    {
        if (!$customerService->exists($customerId)){
            return false;
        }

        if ($rulesetId === null){
            return true;
        }

        return $customerService->hasRuleset($customerId, $rulesetId);
    }
}
