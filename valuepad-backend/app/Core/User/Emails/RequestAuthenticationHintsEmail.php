<?php
namespace ValuePad\Core\User\Emails;
use ValuePad\Core\Support\Letter\Email;
use ValuePad\Core\User\Entities\Token;

class RequestAuthenticationHintsEmail extends Email
{
    /**
     * @var Token[]
     */
    private $tokens;

    /**
     * @param Token[] $tokens
     */
    public function __construct($tokens)
    {
        $this->tokens = $tokens;
    }

    /**
     * @return Token[]
     */
    public function getTokens()
    {
        return $this->tokens;
    }
}
