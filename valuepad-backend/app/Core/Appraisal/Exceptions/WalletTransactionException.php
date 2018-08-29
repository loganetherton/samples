<?php
namespace ValuePad\Core\Appraisal\Exceptions;

use RuntimeException;

class WalletTransactionException extends RuntimeException
{
    /**
     * @var string|int
     */
    private $errorCode;

    public function __construct($message, $code)
    {
        parent::__construct($message);

        $this->errorCode = $code;
    }

    /**
     * @return int|string
     */
    public function getErrorCode()
    {
        return $this->errorCode;
    }
}
