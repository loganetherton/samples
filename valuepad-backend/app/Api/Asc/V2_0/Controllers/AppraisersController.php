<?php
namespace ValuePad\Api\Asc\V2_0\Controllers;

use Illuminate\Http\Response;
use ValuePad\Api\Asc\V2_0\Processors\AppraisersSearchableProcessor;
use ValuePad\Api\Asc\V2_0\Transformers\AppraiserTransformer;
use ValuePad\Api\Support\BaseController;
use ValuePad\Api\Support\DefaultPaginatorAdapter;
use ValuePad\Core\Asc\Services\AscService;
use ValuePad\Core\Shared\Options\PaginationOptions;

class AppraisersController extends BaseController
{
    /**
     * @var AscService
     */
    private $ascService;

    /**
     * @param AscService $ascService
     */
    public function initialize(AscService $ascService)
    {
        $this->ascService = $ascService;
    }

    /**
     * @param AppraisersSearchableProcessor $processor
     * @return Response
     */
    public function index(AppraisersSearchableProcessor $processor)
    {
		$adapter = new DefaultPaginatorAdapter([
			'getAll' => function($page, $perPage) use ($processor){

				return $this->ascService->getAllByCriteria(
					$processor->getCriteria(),
					new PaginationOptions($page, $perPage)
				);
			},
			'getTotal' => function() use ($processor){
				return $this->ascService->getTotalByCriteria($processor->getCriteria());
			}
		]);

        return $this->resource->makeAll(
			$this->paginator($adapter),
			$this->transformer(AppraiserTransformer::class)
		);
    }
}
