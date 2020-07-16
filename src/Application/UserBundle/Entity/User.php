<?php

namespace Application\UserBundle\Entity;

use DateTime;
use Sonata\UserBundle\Entity\BaseUser as BaseUser;

class User extends BaseUser
{
    /** @var int $id */
    protected $id;

    /** @var DateTime */
    protected $deletedAt;

    /**
     * @return int $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return DateTime|null
     */
    public function getDeletedAt(): ?DateTime
    {
        return $this->deletedAt;
    }

    /**
     * @param DateTime|null $deletedAt
     * @return User
     */
    public function setDeletedAt(?DateTime $deletedAt): User
    {
        $this->deletedAt = $deletedAt;
        return $this;
    }

    public function __toString()
    {
        return $this->getEmail() && $this->getEmail() !== 'anonymized'
            ? $this->getEmail()
            : $this->getUsername();
    }

    public function isEnabled()
    {
        if (null !== $this->getDeletedAt()) {
            return false;
        }
        return parent::isEnabled();
    }
}
