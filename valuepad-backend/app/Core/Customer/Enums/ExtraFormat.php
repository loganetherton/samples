<?php
namespace ValuePad\Core\Customer\Enums;

use Ascope\Libraries\Enum\Enum;
use ValuePad\Core\Document\Enums\Format as SourceFormat;

class ExtraFormat extends Enum
{
	const ACI = SourceFormat::ACI;
	const ZAP = SourceFormat::ZAP;
	const ENV = SourceFormat::ENV;
	const ZOO = SourceFormat::ZOO;
}
