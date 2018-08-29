<?php
namespace ValuePad\Api\Language\V2_0\Controllers;

use Illuminate\Http\Response;
use ValuePad\Api\Language\V2_0\Transformers\LanguageTransformer;
use ValuePad\Api\Support\BaseController;
use ValuePad\Core\Language\Services\LanguageService;

/**
 *
 *
 */
class LanguagesController extends BaseController
{

    /**
     *
     * @var LanguageService
     */
    private $languageService;

    /**
     *
     * @param LanguageService $languageService
     */
    public function initialize(LanguageService $languageService)
    {
        $this->languageService = $languageService;
    }

    /**
     *
     * @return Response
     */
    public function index()
    {
        return $this->resource->makeAll($this->languageService->getAll(), $this->transformer(LanguageTransformer::class));
    }
}
