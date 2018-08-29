<?php
namespace ValuePad\Core\Amc\Validation;
use Ascope\Libraries\Validation\AbstractThrowableValidator;
use Ascope\Libraries\Validation\Binder;
use Ascope\Libraries\Validation\Property;
use Ascope\Libraries\Validation\Rules\Callback;
use Ascope\Libraries\Validation\Rules\Greater;
use Ascope\Libraries\Validation\Rules\Obligate;
use Ascope\Libraries\Validation\Rules\Walk;
use ValuePad\Core\Amc\Persistables\FeeByStatePersistable;
use ValuePad\Core\Location\Services\StateService;

class SyncFeesByStateValidator extends AbstractThrowableValidator
{
    /**
     * @var StateService
     */
    private $stateService;

    /**
     * @param StateService $stateService
     */
    public function __construct(StateService $stateService)
    {
        $this->stateService = $stateService;
    }

    /**
     * @param Binder $binder
     * @return void
     */
    protected function define(Binder $binder)
    {
        $binder->bind('data', function(Property $property){
            $property->addRule(new Walk(function(Binder $binder){

                $binder->bind('state', function(Property $property){
                     $property->addRule(new Obligate());
                });

                $binder->bind('amount', function(Property $property){
                    $property
                        ->addRule(new Obligate())
                        ->addRule(new Greater(0));
                });

            }));

            $property->addRule((
                new Callback([$this, 'uniqueStates']))
                    ->setMessage('The state must be unique in the current collection.')
                    ->setIdentifier('unique')
            );

            $property->addRule(
                (new Callback([$this, 'existStates']))
                    ->setMessage('One of the provided state does not exist in the system.')
                    ->setIdentifier('exists')
            );
        });
    }

    /**
     * @param FeeByStatePersistable[] $persistables
     * @return bool
     */
    public function uniqueStates(array $persistables)
    {
        $values = [];

        foreach ($persistables as $persistable){
            $values[$persistable->getState()] = true;
        }

        return count($persistables) === count($values);
    }

    /**
     * @param FeeByStatePersistable[] $persistables
     * @return bool
     */
    public function existStates(array $persistables)
    {
        return $this->stateService->existSelected(array_map(function(FeeByStatePersistable $persistable){
            return $persistable->getState();
        }, $persistables));
    }
}
