<?php
namespace ValuePad\DAL\Shared\Support;

use ValuePad\Core\Shared\Interfaces\TokenGeneratorInterface;

class TokenGenerator implements TokenGeneratorInterface
{
    /**
     * @return string
     */
    public function generate()
    {
        return str_random(64);
    }
}
