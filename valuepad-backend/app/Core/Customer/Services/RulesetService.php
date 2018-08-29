<?php
namespace ValuePad\Core\Customer\Services;
use Ascope\Libraries\Validation\PresentableException;
use ValuePad\Core\Appraisal\Entities\Order;
use ValuePad\Core\Customer\Entities\Customer;
use ValuePad\Core\Customer\Entities\Rules;
use ValuePad\Core\Customer\Entities\Ruleset;
use ValuePad\Core\Customer\Persistables\RulesetPersistable;
use ValuePad\Core\Customer\Validation\RulesetValidator;
use ValuePad\Core\Location\Entities\State;
use ValuePad\Core\Support\Service\AbstractService;

class RulesetService extends AbstractService
{
    /**
     * @param int $customerId
     * @param RulesetPersistable $persistable
     * @return Ruleset
     */
    public function create($customerId, RulesetPersistable $persistable)
    {
        (new RulesetValidator($this->container))->validate($persistable);

        $rules = new Rules();

        $this->entityManager->persist($rules);

        $this->entityManager->flush();

        $ruleset = new Ruleset();
        $ruleset->giveRules($rules);

        /**
         * @var Customer $customer
         */
        $customer = $this->entityManager->getReference(Customer::class, $customerId);

        $ruleset->setCustomer($customer);

        $this->exchange($persistable, $ruleset);

        $this->entityManager->persist($ruleset);

        $this->entityManager->flush();

        return $ruleset;
    }

    /**
     * @param $id
     * @param RulesetPersistable $persistable
     */
    public function update($id, RulesetPersistable $persistable)
    {
        (new RulesetValidator($this->container))->validate($persistable, true);

        /**
         * @var Ruleset $ruleset
         */
        $ruleset = $this->entityManager->find(Ruleset::class, $id);

        $this->exchange($persistable, $ruleset);

        $this->entityManager->flush();
    }

    /**
     * @param RulesetPersistable $persistable
     * @param Ruleset $ruleset
     */
    private function exchange(RulesetPersistable $persistable, Ruleset $ruleset)
    {
        $this->transfer($persistable, $ruleset, [
            'ignore' => [
                'rules'
            ]
        ]);

        $rules = $ruleset->takeRules();

        $rules->reset();

        foreach ($persistable->getRules() as $name => $value){
            $rules->addAvailable($name);

            if (in_array($name, ['clientState', 'clientDisplayedOnReportState'])){

                if ($value !== null){
                    $value = $this->entityManager->find(State::class, $value);
                }
            }

            $rules->{'set'.$name}($value);
        }
    }

    /**
     * @param int $id
     * @return Ruleset
     */
    public function get($id)
    {
        return $this->entityManager->find(Ruleset::class, $id);
    }

    /**
     * @param int $id
     */
    public function delete($id)
    {
        if ($this->entityManager->getRepository(Order::class)->exists(['rulesets' => ['HAVE MEMBER', $id]])){
            throw new PresentableException('Unable to delete the provided ruleset since it is assigned to an order.');
        }

        /**
         * @var Ruleset $ruleset
         */
        $ruleset = $this->entityManager->getReference(Ruleset::class, $id);

        $this->entityManager->remove($ruleset);

        $this->entityManager->flush();
    }
}
