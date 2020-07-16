<?php


namespace Core\ClassificationBundle\Entity;


class Context extends \Sonata\ClassificationBundle\Entity\BaseContext
{
    public function getId()
    {
        return $this->id;
    }
}