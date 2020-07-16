<?php

namespace Core\ClassificationBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection as CollectionInterface;
use FOS\UserBundle\Model\GroupInterface;
use Sonata\ClassificationBundle\Model\Category as BaseCategory;


class Category extends BaseCategory
{
    /** @var integer */
    protected $id;

    /** @var string */
    protected $path;

    /** @var integer */
    protected $level;

    /** @var \DateTime */
    protected $treeLockTime;

    /** @var string */
    protected $treePathHash;

    public function __construct()
    {
        parent::__construct();
        $this->enabled = true;
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    public function disableChildrenLazyLoading()
    {
        if (is_object($this->children)) {
            $this->children->setInitialized(true);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setName($name)
    {
        if ('n-a' === $this->getSlug() || !$this->getSlug() || $this->getSlug() === Tag::slugify($this->getName())) {
            $this->setSlug(Tag::slugify($name));
        }

        $this->name = $name;
    }

    public function setLevel($level)
    {
        $this->level = $level;
    }

    public function getLevel()
    {
        return $this->level;
    }

    public function setPath($path)
    {
        $this->path = $path;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function setTreeLockTime($treeLockTime)
    {
        $this->treeLockTime = $treeLockTime;
    }

    public function getTreeLockTime()
    {
        return $this->treeLockTime;
    }

    public function setTreePathHash($treePathHash)
    {
        $this->treePathHash = $treePathHash;
    }

    public function getTreePathHash()
    {
        return $this->treePathHash;
    }

    public function getId()
    {
        return $this->id;
    }

    public function buildPathCategories()
    {
        $result = array($this);
        $current = $this;
        while($current = $current->getParent()) {
            $result[] = $current;
        }

        return array_reverse($result);
    }

    /**
     * {@inheritdoc}
     */
    public function setSlug($slug)
    {
        if (null === $slug) {
            return;
        }
        parent::setSlug($slug);
    }


}
