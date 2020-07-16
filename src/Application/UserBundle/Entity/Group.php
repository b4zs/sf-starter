<?php

namespace Application\UserBundle\Entity;

use Sonata\UserBundle\Entity\BaseGroup as BaseGroup;

class Group extends BaseGroup
{
    /**
     * @var int $id
     */
    protected $id;

    /**
     * Get id.
     *
     * @return int $id
     */
    public function getId()
    {
        return $this->id;
    }
}
