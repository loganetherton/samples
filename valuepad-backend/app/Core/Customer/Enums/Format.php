<?php
namespace ValuePad\Core\Customer\Enums;

use Ascope\Libraries\Enum\Enum;
use ValuePad\Core\Document\Enums\Format as SourceFormat;
class Format extends Enum
{
	const PDF = SourceFormat::PDF;
	const XML = SourceFormat::XML;
	const ENV = SourceFormat::ENV;
}
