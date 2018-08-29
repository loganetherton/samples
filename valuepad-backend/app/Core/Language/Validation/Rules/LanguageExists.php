<?php
namespace ValuePad\Core\Language\Validation\Rules;

use Ascope\Libraries\Validation\Error;
use Ascope\Libraries\Validation\Rules\AbstractRule;
use Ascope\Libraries\Validation\Value;
use ValuePad\Core\Language\Services\LanguageService;

/**
 *
 *
 */
class LanguageExists extends AbstractRule
{

    /**
     *
     * @var LanguageService
     */
    private $languageService;

    public function __construct(LanguageService $languageService)
    {
        $this->languageService = $languageService;

        $this->setIdentifier('exists');
        $this->setMessage('The language with the specified code does not exist.');
    }

    /**
     *
     * @param mixed|Value $value
     * @return Error|null
     */
    public function check($value)
    {
        if (! $this->languageService->exists($value)) {
            return $this->getError();
        }

        return null;
    }
}
