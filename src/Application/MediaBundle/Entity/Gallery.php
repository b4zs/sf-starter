<?php

namespace Application\MediaBundle\Entity;

use Core\ClassificationBundle\Entity\Tag;
use Doctrine\Common\Collections\ArrayCollection;
use Sonata\ClassificationBundle\Model\TagInterface;

class Gallery extends \Core\MediaBundle\Entity\BaseGallery
{
    /**
     * @var int $id
     */
    protected $id;

    /**
     * @var boolean
     */
    private $enabledForIndexSlider;

    /** @var ArrayCollection|Tag[] */
    private $tags;

    public function __construct()
    {
        parent::__construct();

        $this->tags = new ArrayCollection();
    }


    /**
     * Get id.
     *
     * @return int $id
     */
    public function getId()
    {
        return $this->id;
    }

    public function isEnabledForIndexSlider()
    {
        return $this->enabledForIndexSlider;
    }

    public function setEnabledForIndexSlider($enabledForIndexSlider)
    {
        $this->enabledForIndexSlider = $enabledForIndexSlider;
    }

    public function getTags()
    {
        return $this->tags;
    }

    public function addTag(TagInterface $tag)
    {
        $this->tags->add($tag);
    }

    public function removeTag(TagInterface $tag)
    {
        $this->tags->removeElement($tag);
    }
}
