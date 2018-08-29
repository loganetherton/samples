<?php
namespace ValuePad\Core\User\Entities;

use ValuePad\Core\Shared\Properties\IdPropertyTrait;
use ValuePad\Core\User\Properties\Device\PlatformPropertyTrait;
use ValuePad\Core\User\Properties\Device\TokenPropertyTrait;
use ValuePad\Core\User\Properties\UserPropertyTrait;

class Device
{
	use IdPropertyTrait;
	use UserPropertyTrait;
	use TokenPropertyTrait;
	use PlatformPropertyTrait;
}
