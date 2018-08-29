<?php
namespace ValuePad\DAL\Asc\Types;

use ValuePad\Core\Asc\Enums\Certification;
use ValuePad\Core\Asc\Enums\Certifications;
use ValuePad\DAL\Support\EnumType;

class CertificationsType extends EnumType
{
    /**
     * @return string
     */
    protected function getEnumClass()
    {
        return Certification::class;
    }

	/**
	 * @return string
	 */
	protected function getEnumCollectionClass()
	{
		return Certifications::class;
	}
}
