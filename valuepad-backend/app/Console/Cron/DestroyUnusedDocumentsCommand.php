<?php
namespace ValuePad\Console\Cron;

use ValuePad\Core\Document\Services\DocumentService;

class DestroyUnusedDocumentsCommand extends AbstractCommand
{
	/**
	 * @param DocumentService $documentService
	 */
	public function fire(DocumentService $documentService)
	{
		$documentService->deleteAllUnused();
	}
}
