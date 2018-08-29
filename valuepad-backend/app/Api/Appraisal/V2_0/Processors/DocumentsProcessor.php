<?php
namespace ValuePad\Api\Appraisal\V2_0\Processors;

use ValuePad\Api\Support\BaseProcessor;
use ValuePad\Core\Appraisal\Persistables\DocumentPersistable;
use ValuePad\Core\Customer\Entities\Customer;
use ValuePad\Core\Session\Entities\Session;

class DocumentsProcessor extends BaseProcessor
{
	/**
	 * @return array
	 */
	protected function configuration()
	{
		$config = [
			'primary' => 'document',
			'primaries' => 'document[]',
			'extra' => 'document[]'
		];

		/**
		 * @var Session $session
		 */
		$session = $this->container->make(Session::class);

		if ($session->getUser() instanceof Customer){
			$config['showToAppraiser'] = 'bool';
		}

		return $config;
	}

	/**
	 * @return DocumentPersistable
	 */
	public function createPersistable()
	{
		return $this->populate(new DocumentPersistable());
	}
}
