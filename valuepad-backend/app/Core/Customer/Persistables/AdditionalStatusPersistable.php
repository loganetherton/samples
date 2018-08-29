<?php
namespace ValuePad\Core\Customer\Persistables;

class AdditionalStatusPersistable
{
    /**
     * @var string
     */
    private $title;
    public function setTitle($title) { $this->title = $title; }
    public function getTitle() { return $this->title; }

    /**
     * @var string
     */
    private $comment;
    public function setComment($comment) { $this->comment = $comment; }
    public function getComment() { return $this->comment; }
}
