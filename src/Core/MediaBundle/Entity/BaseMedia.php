<?php


namespace Core\MediaBundle\Entity;

use Application\MediaBundle\Entity\GalleryHasMedia;
use Application\PageBundle\Entity\Site;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityNotFoundException;
use Sonata\ClassificationBundle\Model\TagInterface;

abstract class BaseMedia extends \Sonata\MediaBundle\Entity\BaseMedia
{
    /** @var  ArrayCollection|TagInterface[] */
    protected $tags;

    /** @var  \DateTime */
    protected $deletedAt;

    private $urls = [];

    public function __construct()
    {
        $this->tags = new ArrayCollection();
        $this->enabled = true;
    }

    public function __toString()
    {
        try {
            return $this->getName() ?: 'n/a';
        } catch (EntityNotFoundException $e) {
            return '(removed)';
        }
    }

    public function getTags()
    {
        return $this->tags;
    }

    public function setTags($tags)
    {
        $this->tags = $tags;
    }

    /**
     * @return \DateTime
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    /**
     * @param \DateTime $deletedAt
     */
    public function setDeletedAt($deletedAt)
    {
        $this->deletedAt = $deletedAt;
    }

    public function setGalleryHasMedias($galleryHasMedias)
    {
        parent::setGalleryHasMedias($galleryHasMedias);

        /** @var GalleryHasMedia $galleryHasMedias */
        foreach ($this->galleryHasMedias as $galleryHasMedias) {
            $galleryHasMedias->setMedia($this);
        }
    }

    public function getEmbedUrl()
    {
        if (in_array($this->getProviderName(), array('sonata.media.provider.youtube'))) {
            $providerData = $this->getProviderMetadata();
            if (isset($providerData['html']) && preg_match('/src="([\w+\d+\/\.:#_=?]+)"/', $providerData['html'], $out)) {
                return $out[1];
            }
        }

        return false;
    }

    public function getUrls()
    {
        return $this->urls;
    }

    public function setUrls($urls)
    {
        $this->urls = $urls;
    }
}
