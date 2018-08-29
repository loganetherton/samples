<?php
namespace ValuePad\Core\Customer\Persistables;

class JobTypePersistable
{
    /**
     * @var bool
     */
    private $isCommercial;
    public function setCommercial($flag) { $this->isCommercial = $flag; }
    public function isCommercial() { return $this->isCommercial; }

    /**
     * @var bool
     */
    private $isPayable;
    public function setPayable($flag) { $this->isPayable = $flag; }
    public function isPayable() { return $this->isPayable; }


    /**
     * @var string
     */
    private $title;
    public function setTitle($title) { $this->title = $title; }
    public function getTitle() { return $this->title; }

	/**
	 * @var int
	 */
	private $local;
	public function setLocal($local) { $this->local = $local; }
	public function getLocal() { return $this->local; }
}
