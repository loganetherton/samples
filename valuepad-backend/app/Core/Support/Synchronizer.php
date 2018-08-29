<?php
namespace ValuePad\Core\Support;

class Synchronizer
{
    /**
     * @var callable
     */
    private $identify1;

    /**
     * @var callable
     */
    private $identify2;

    /**
     * @var callable
     */
    private $onRemove;

    /**
     * @var callable
     */
    private $onUpdate;

    /**
     * @var callable
     */
    private $onCreate;


    /**
     * @param callable $callback
     * @return $this
     */
    public function identify1(callable  $callback)
    {
        $this->identify1 = $callback;

        return $this;
    }

    /**
     * @param callable $callback
     * @return $this
     */
    public function identify2(callable  $callback)
    {
        $this->identify2 = $callback;

        return $this;
    }

    /**
     * @param callable $callback
     * @return $this
     */
    public function onRemove(callable  $callback)
    {
        $this->onRemove = $callback;
        return $this;
    }

    /**
     * @param callable $callback
     * @return $this
     */
    public function onUpdate(callable  $callback)
    {
        $this->onUpdate = $callback;
        return $this;
    }

    /**
     * @param callable $callback
     * @return $this
     */
    public function onCreate(callable  $callback)
    {
        $this->onCreate = $callback;
        return $this;
    }

    /**
     * @param object[] $source1
     * @param object[] $source2
     * @return object[]
     */
    public function synchronize($source1, $source2)
    {
        $source1 = array_define_keys($source1, $this->identify1);
        $source2 = array_define_keys($source2, $this->identify2);

        $result = [];

        foreach ($source1 as $identity => $object){
            if (!isset($source2[$identity])){
                call_user_func($this->onRemove, $object);
            } else {
                call_user_func($this->onUpdate, $object, $source2[$identity]);
                $result[] = $object;
            }
        }

        foreach ($source2 as $identity => $object){
            if (!isset($source1[$identity])){
                $result[] = call_user_func($this->onCreate, $object);
            }
        }

        return $result;
    }
}
