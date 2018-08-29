<?php
namespace ValuePad\Core\Company\Entities;

class Permission
{
    /**
     * @var int
     */
    private $id;
    public function setId($id) { $this->id = $id; }
    public function getId() { return $this->id; }

    /**
     * @var Staff
     */
    private $manager;
    public function setManager(Staff $manager) { $this->manager = $manager; }
    public function getManager() { return $this->manager; }

    /**
     * @var Staff
     */
    private $appraiser;
    public function setAppraiser(Staff $appraiser) { $this->appraiser = $appraiser; }
    public function getAppraiser() { return $this->appraiser; }

}
