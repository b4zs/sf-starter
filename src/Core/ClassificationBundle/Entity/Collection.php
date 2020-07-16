<?php


namespace Core\ClassificationBundle\Entity;


use Sonata\ClassificationBundle\Entity\BaseCollection;

class Collection extends BaseCollection
{
    /**
     * @var integer $id
     */
    protected $id;


    /** @var  ObjectIdentity */
    private $owner;

    public function getId()
    {
        return $this->id;
    }
} 
