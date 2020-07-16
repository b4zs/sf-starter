<?php

namespace Core\ClassificationBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Sonata\ClassificationBundle\Entity\BaseTag as BaseTag;

class Tag extends BaseTag
{
	/**
	 * @var integer $id
	 */
	protected $id;

    /** @var  ArrayCollection */
    protected $media;

    /** @var  ArrayCollection */
    protected $pages;

    /** @var  ArrayCollection */
    protected $contents;

    function __construct()
    {
        $this->media = new ArrayCollection();
        $this->pages = new ArrayCollection();
        $this->contents = new ArrayCollection();
        $this->enabled = true;
    }

	public function getId()
	{
		return $this->id;
	}

    public function getMedia()
    {
        return $this->media;
    }

    public function setMedia($media)
    {
        $this->media = $media;
    }

	/**
	 * @return ArrayCollection
	 */
	public function getPages()
	{
		return $this->pages;
	}

	/**
	 * @param ArrayCollection $pages
	 */
	public function setPages($pages)
	{
		$this->pages = $pages;
	}

}