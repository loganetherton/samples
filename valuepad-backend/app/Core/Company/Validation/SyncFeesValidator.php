<?php
namespace ValuePad\Core\Company\Validation;
use Ascope\Libraries\Validation\AbstractThrowableValidator;
use Ascope\Libraries\Validation\Binder;
use Ascope\Libraries\Validation\Property;
use Ascope\Libraries\Validation\Rules\Callback;
use Ascope\Libraries\Validation\Rules\Greater;
use Ascope\Libraries\Validation\Rules\Obligate;
use Ascope\Libraries\Validation\Rules\Walk;
use ValuePad\Core\Company\Persistables\FeePersistable;
use ValuePad\Core\JobType\Services\JobTypeService;

class SyncFeesValidator extends AbstractThrowableValidator
{
    /**
     * @var JobTypeService
     */
    private $jobTypeService;

    /**
     * @var array
     */
    private $cachedJobTypeIds;

    /**
     * @param JobTypeService $jobTypeService
     */
    public function __construct(JobTypeService $jobTypeService)
    {
        $this->jobTypeService = $jobTypeService;
    }

    /**
     * @param Binder $binder
     * @return void
     */
    protected function define(Binder $binder)
    {
        $binder->bind('data', function(Property $property){

            $jobTypesUnique = new Callback([$this, 'checkIfJobTypesUnique']);

            $jobTypesUnique
                ->setIdentifier('unique')
                ->setMessage('The provided job types must be unique in the collection.');

            $jobTypesExist = new Callback([$this, 'checkIfJobTypesExist']);

            $jobTypesExist
                ->setIdentifier('exists')
                ->setMessage('One of the provided job types was not found in the system.');

            $property
                ->addRule($jobTypesUnique)
                ->addRule($jobTypesExist)
                ->addRule(new Walk(function(Binder $binder){
                    $binder->bind('amount', function(Property $property){
                        $property
                            ->addRule(new Obligate())
                            ->addRule(new Greater(0));
                    });
                }));
        });
    }

    /**
     * @param FeePersistable[] $persistables
     * @return bool
     */
    public function checkIfJobTypesUnique(array $persistables)
    {
        $ids = $this->extractJobTypeIds($persistables);

        return count($ids) === count(array_unique($ids));
    }

    /**
     * @param FeePersistable[] $persistables
     * @return array
     */
    public function checkIfJobTypesExist(array $persistables)
    {
        return $this->jobTypeService->existSelected($this->extractJobTypeIds($persistables));
    }


    /**
     * @param FeePersistable[] $persistables
     * @return array
     */
    private function extractJobTypeIds(array $persistables)
    {
        if ($this->cachedJobTypeIds === null){
            $this->cachedJobTypeIds = array_map(function(FeePersistable $persistable){
                return $persistable->getJobType();
            }, $persistables);
        }

        return $this->cachedJobTypeIds;
    }
}
