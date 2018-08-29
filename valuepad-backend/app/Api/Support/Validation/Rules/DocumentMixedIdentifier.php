<?php
namespace ValuePad\Api\Support\Validation\Rules;

use Ascope\Libraries\Validation\Rules\IntegerCast;
use Ascope\Libraries\Validation\Rules\Mixed;

/**
 *
 *
 */
class DocumentMixedIdentifier extends Mixed
{

    public function __construct()
    {
        parent::__construct([
            new IntegerCast(),
            new DocumentHashIdentifier()
        ]);

        $this->setIdentifier('cast');
        $this->setMessage('The document identifier must be int or hash consisting of id and token.');
    }
}
