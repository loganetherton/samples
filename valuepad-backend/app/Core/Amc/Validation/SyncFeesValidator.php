<?php
namespace ValuePad\Core\Amc\Validation;
use Ascope\Libraries\Validation\AbstractThrowableValidator;
use Ascope\Libraries\Validation\Binder;
use Ascope\Libraries\Validation\Property;
use Ascope\Libraries\Validation\Rules\Callback;
use Ascope\Libraries\Validation\Rules\Greater;
use Ascope\Libraries\Validation\Rules\Obligate;
use Ascope\Libraries\Validation\Rules\Walk;
use ValuePad\Core\Assignee\Persistables\FeePersistable;
use ValuePad\Core\JobType\Services\JobTypeService;

class SyncFeesValidator extends AbstractThrowableValidator
{
    /**
     * @var JobTypeService
     */
    private $jobTypesService;

    /**
     * @param JobTypeService $jobTypeService
     */
    public function __construct(JobTypeService $jobTypeService)
    {
        $this->jobTypesService = $jobTypeService;
    }

    /**
     * @param Binder $binder
     * @return void
     */
    protected function define(Binder $binder)
    {
        $binder->bind('data', function(Property $property){

            $property->addRule(new Walk(function(Binder $binder){

                $binder->bind('jobType', function(Property $property){
                    $property->addRule(new Obligate());
                });

                $binder->bind('amount', function(Property $property){
                    $property->addRule(new Obligate());
                    $property->addRule(new Greater(0));
                });
            }));

            $property->addRule((new Callback([$this, 'uniqueJobTypes']))
                ->setMessage('The job types must be unique in the current collection.')
                ->setIdentifier('unique')
            );

            $property->addRule((new Callback([$this, 'existJobTypes']))
                ->setMessage('One of the provided job types does not exist in the system.')
                ->setIdentifier('exists')
            );
        });
    }

    /**
     * @param FeePersistable[] $persistables
     * @return bool
     */
    public function uniqueJobTypes(array $persistables)
    {
        $values = [];

        foreach ($persistables as $persistable){
            $values[$persistable->getJobType()] = true;
        }

        return count($persistables) === count($values);
    }

    /**
     * @param FeePersistable[] $persistables
     * @return bool
     */
    public function existJobTypes(array $persistables)
    {
        return $this->jobTypesService->existSelected(array_map(function(FeePersistable $persistable){
            return $persistable->getJobType();
        }, $persistables));
    }
}
