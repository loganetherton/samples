<?php
namespace ValuePad\Api\User\V2_0\Transformers;
use ValuePad\Api\Support\TransformerModifiers;
use ValuePad\Core\User\Entities\User;

class TypeCalculatedProperty
{
    /**
     * @var TransformerModifiers
     */
    private $modifier;

    /**
     * @param TransformerModifiers $modifier
     */
    public function __construct(TransformerModifiers $modifier)
    {
        $this->modifier = $modifier;
    }

    /**
     * @param User $user
     * @return string
     */
    public function __invoke(User $user)
    {
        return $this->modifier->stringable($user);
    }
}
