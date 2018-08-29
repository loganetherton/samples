<?php
namespace ValuePad\Core\User\Persistables;

use ValuePad\Core\User\Properties\Device\PlatformPropertyTrait;
use ValuePad\Core\User\Properties\Device\TokenPropertyTrait;

class DevicePersistable
{
	use TokenPropertyTrait;
	use PlatformPropertyTrait;
}
