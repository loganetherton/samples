<?php
namespace ValuePad\Api\Customer\V2_0\Controllers;

use Illuminate\Http\Response;
use ValuePad\Api\Customer\V2_0\Processors\ClientsProcessor;
use ValuePad\Api\Customer\V2_0\Transformers\ClientTransformer;
use ValuePad\Api\Support\BaseController;
use ValuePad\Api\Support\DefaultPaginatorAdapter;
use ValuePad\Core\Customer\Options\FetchClientsOptions;
use ValuePad\Core\Customer\Services\ClientService;
use ValuePad\Core\Customer\Services\CustomerService;
use ValuePad\Core\Shared\Options\PaginationOptions;

class ClientsController extends BaseController
{
    /**
     * @var ClientService
     */
    private $clientService;

    /**
     * @param ClientService $clientService
     */
    public function initialize(ClientService $clientService)
    {
        $this->clientService = $clientService;
    }

    /**
     * @param int $customerId
     * @return Response
     */
    public function index($customerId)
    {
        $adapter = new DefaultPaginatorAdapter([
            'getAll' => function($page, $perPage) use ($customerId){
                $options = new FetchClientsOptions();
                $options->setPagination(new PaginationOptions($page, $perPage));
                return $this->clientService->getAll($customerId, $options);
            },
            'getTotal' => function() use ($customerId){
                return $this->clientService->getTotal($customerId);
            }
        ]);

        return $this->resource->makeAll(
            $this->paginator($adapter),
            $this->transformer(ClientTransformer::class)
        );
    }

    /**
     * @param int $customerId
     * @param ClientsProcessor $processor
     * @return Response
     */
    public function store($customerId, ClientsProcessor $processor)
    {
        return $this->resource->make(
            $this->clientService->create($customerId, $processor->createPersistable()),
            $this->transformer(ClientTransformer::class)
        );
    }

    /**
     * @param int $customerId
     * @param int $clientId
     * @return Response
     */
    public function show($customerId, $clientId)
    {
        return $this->resource->make(
            $this->clientService->get($clientId),
            $this->transformer(ClientTransformer::class)
        );
    }

    /**
     * @param int $customerId
     * @param int $clientId
     * @param ClientsProcessor $processor
     * @return Response
     */
    public function update($customerId, $clientId, ClientsProcessor $processor)
    {
        $this->clientService->update($clientId, $processor->createPersistable(), $processor->schedulePropertiesToClear());

        return $this->resource->blank();
    }

    /**
     * @param CustomerService $customerService
     * @param int $customerId
     * @param int $clientId
     * @return bool
     */
    public static function verifyAction(CustomerService $customerService, $customerId, $clientId = null)
    {
        if (!$customerService->exists($customerId)){
            return false;
        }

        if ($clientId === null){
            return true;
        }

        return $customerService->hasClient($customerId, $clientId);
    }
}
