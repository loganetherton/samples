<?php
namespace ValuePad\Api\Appraiser\V2_0\Controllers;

use Ascope\Libraries\Validation\Error;
use Ascope\Libraries\Validation\ErrorsThrowableCollection;
use Ascope\Libraries\Verifier\Action;
use Illuminate\Http\Response;
use ValuePad\Api\Appraisal\V2_0\Support\DocumentsTrait;
use ValuePad\Api\Appraisal\V2_0\Transformers\DocumentTransformer;
use ValuePad\Api\Appraisal\V2_0\Processors\DocumentsProcessor;
use ValuePad\Api\Appraiser\V2_0\Processors\EmailProcessor;
use ValuePad\Api\Assignee\V2_0\Transformers\DocumentSupportedFormatsTransformer;
use ValuePad\Api\Support\BaseController;
use ValuePad\Core\Appraisal\Services\DocumentService;
use ValuePad\Core\Appraisal\Services\OrderService;
use ValuePad\Core\Appraiser\Services\AppraiserService;
use ValuePad\Core\Shared\Exceptions\InvalidEmailException;

class DocumentController extends BaseController
{
	use DocumentsTrait;

	/**
	 * @var DocumentService
	 */
	private $documentService;

	/**
	 * @var OrderService
	 */
	private $orderService;

	/**
	 * @param DocumentService $documentService
	 * @param OrderService $orderService
	 */
	public function initialize(
		DocumentService $documentService,
		OrderService $orderService
	)
	{
		$this->documentService = $documentService;
		$this->orderService = $orderService;
	}

	/**
	 * @param int $appraiserId
	 * @param int $orderId
	 * @param DocumentsProcessor $processor
	 * @return Response
	 */
	public function store($appraiserId, $orderId, DocumentsProcessor $processor)
	{
		return $this->resource->make($this->tryCreate(function() use ($orderId, $processor){
			return $this->orderService->proceedWithDocument($orderId, $processor->createPersistable());
		}), $this->transformer(DocumentTransformer::class));
	}

	/**
	 * @param int $appraiserId
	 * @param int $orderId
	 * @param DocumentsProcessor $processor
	 * @return Response
	 */
	public function update($appraiserId, $orderId, DocumentsProcessor $processor)
	{
		$this->documentService->updateRecent($orderId, $processor->createPersistable());

		return $this->resource->blank();
	}

	/**
	 * @param $appraiserId
	 * @param $orderId
	 * @return Response
	 */
	public function show($appraiserId, $orderId)
	{
		return $this->resource->make(
			$this->documentService->getRecent($orderId),
			$this->transformer(DocumentTransformer::class)
		);
	}

	/**
	 * @param int $appraiserId
	 * @param int $orderId
	 * @return Response
	 */
	public function formats($appraiserId, $orderId)
	{
		return $this->resource->make(
			$this->documentService->getSupportedFormats($orderId),
			$this->transformer(DocumentSupportedFormatsTransformer::class)
		);
	}

	/**
	 * @param int $appraiserId
	 * @param int $orderId
	 * @param EmailProcessor $processor
	 * @return Response
	 * @throws ErrorsThrowableCollection
	 */
	public function email($appraiserId, $orderId, EmailProcessor $processor)
	{
		try {
			$this->documentService->emailRecentOnAppraiserBehalf($orderId, $processor->getEmail());
		} catch (InvalidEmailException $ex){
			$errors = new ErrorsThrowableCollection();
			$errors['email'] = new Error('format', $errors->getMessage());
			throw $errors;
		}

		return $this->resource->blank();
	}

	/**
	 * @param Action $action
	 * @param AppraiserService $appraiserService
	 * @param DocumentService $documentService
	 * @param int $appraiserId
	 * @param int $orderId
	 * @return bool
	 */
	public static function verifyAction(
		Action $action,
		AppraiserService $appraiserService,
		DocumentService  $documentService,
		$appraiserId,
		$orderId
	)
	{
		if (!$appraiserService->exists($appraiserId)){
			return false;
		}

		if (!$appraiserService->hasOrder($appraiserId, $orderId)){
			return false;
		}

		if ($action->is(['store', 'formats'])){
			return true;
		}

		return $documentService->existsRecent($orderId);
	}
}
