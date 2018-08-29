<?php
namespace ValuePad\Api\Amc\V2_0\Controllers;
use Illuminate\Http\Response;
use ValuePad\Api\Amc\V2_0\Processors\InvoicesSearchableProcessor;
use ValuePad\Api\Amc\V2_0\Processors\PayInvoiceProcessor;
use ValuePad\Api\Support\BaseController;
use ValuePad\Api\Support\DefaultPaginatorAdapter;
use ValuePad\Core\Amc\Options\FetchInvoicesOptions;
use ValuePad\Core\Amc\Services\AmcService;
use ValuePad\Core\Amc\Services\InvoiceService;
use ValuePad\Core\Shared\Options\PaginationOptions;

class InvoicesController extends BaseController
{
    /**
     * @var InvoiceService
     */
    private $invoiceService;

    /**
     * @param InvoiceService $invoiceService
     */
    public function initialize(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    /**
     * @param int $amcId
     * @param InvoicesSearchableProcessor $processor
     * @return Response
     */
    public function index($amcId, InvoicesSearchableProcessor $processor)
    {
        $adapter = new DefaultPaginatorAdapter([
            'getAll' => function($page, $perPage) use ($amcId, $processor){

                $options = new FetchInvoicesOptions();
                $options->setCriteria($processor->getCriteria());
                $options->setPagination(new PaginationOptions($page, $perPage));
                $options->setSortables($processor->createSortables());

                return $this->invoiceService->getAll($amcId, $options);
            },
            'getTotal' => function() use ($amcId, $processor){
                return $this->invoiceService->getTotal($amcId, $processor->getCriteria());
            }
        ]);

        return $this->resource->makeAll($this->paginator($adapter), $this->transformer());
    }

    /**
     * @param int $amcId
     * @param int $invoiceId
     * @param PayInvoiceProcessor  $processor
     * @return Response
     */
    public function pay($amcId, $invoiceId, PayInvoiceProcessor $processor)
    {
        $this->invoiceService->pay($invoiceId, $processor->getMeans());

        return $this->resource->blank();
    }

    /**
     * @param AmcService $amcService
     * @param int $amcId
     * @param int $invoiceId
     * @return bool
     */
    public static function verifyAction(AmcService $amcService, $amcId, $invoiceId = null)
    {
        if (!$amcService->exists($amcId)){
            return false;
        }

        if ($invoiceId === null){
            return true;
        }

        return $amcService->hasInvoice($amcId, $invoiceId);
    }
}
