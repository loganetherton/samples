<?php
namespace ValuePad\Api\Appraisal\V2_0\Transformers;

use ValuePad\Api\Support\BaseTransformer;
use ValuePad\Core\Appraisal\Entities\Revision;

class RevisionTransformer extends BaseTransformer
{
	/**
	 * @param Revision $revision
	 * @return array
	 */
	public function transform($revision)
	{
		return $this->extract($revision);
	}
}
