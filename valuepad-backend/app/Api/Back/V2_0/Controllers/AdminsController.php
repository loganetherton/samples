<?php
namespace ValuePad\Api\Back\V2_0\Controllers;
use Illuminate\Http\Response;
use ValuePad\Api\Back\V2_0\Processors\AdminsProcessor;
use ValuePad\Api\Back\V2_0\Transformers\AdminTransformer;
use ValuePad\Api\Support\BaseController;
use ValuePad\Core\Back\Services\AdminService;

class AdminsController extends BaseController
{
    /**
     * @var AdminService
     */
    private $adminService;

    /**
     * @param AdminService $adminService
     */
    public function initialize(AdminService $adminService)
    {
        $this->adminService = $adminService;
    }

    /**
     * @param AdminsProcessor $processor
     * @return Response
     */
    public function store(AdminsProcessor $processor)
    {
        return $this->resource->make(
            $this->adminService->create($processor->createPersistable()),
            $this->transformer(AdminTransformer::class)
        );
    }

    /**
     * @param int $id
     * @return Response
     */
    public function show($id)
    {
        return $this->resource->make(
            $this->adminService->get($id),
            $this->transformer(AdminTransformer::class)
        );
    }

    /**
     * @param int $id
     * @param AdminsProcessor $processor
     * @return Response
     */
    public function update($id, AdminsProcessor $processor)
    {
        $this->adminService->update($id, $processor->createPersistable());

        return $this->resource->blank();
    }

    /**
     * @param AdminService $adminService
     * @param int $id
     * @return bool
     */
    public static function verifyAction(AdminService $adminService, $id)
    {
        return $adminService->exists($id);
    }
}
