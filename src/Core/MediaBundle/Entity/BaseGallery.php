<?php


namespace Core\MediaBundle\Entity;

use Application\MediaBundle\Entity\Gallery;
use Application\MediaBundle\Entity\GalleryHasMedia;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\PersistentCollection;
use Sonata\MediaBundle\Entity\BaseGallery as BaseSonataGallery;
use Sonata\MediaBundle\Model\GalleryHasMediaInterface;
use Sonata\MediaBundle\Model\MediaInterface;

abstract class BaseGallery extends BaseSonataGallery
{
    /** @var null|Media */
    protected $primaryMedia = null;

    protected $description;

    /** @var  \DateTime */
    protected $deletedAt;

    public function __construct()
    {
        parent::__construct();

        $this->setContext('default');
        $this->setDefaultFormat('default_large');
        $this->setEnabled(true);

    }

    /**
     * @return string
     */
    public function getCanonicalName()
    {
        return $this->getName();
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

    public function getPrimaryMedia()
    {
        try {
            if ($this->primaryMedia instanceof MediaInterface && null === $this->primaryMedia->getDeletedAt()) {
                return $this->primaryMedia;
            }
        } catch (EntityNotFoundException $e) {
        }

        return null;
    }

    /**
     * @param Media $primaryMedia
     */
    public function setPrimaryMedia($primaryMedia)
    {
        $this->primaryMedia = $primaryMedia;
    }

    public function getGalleryHasMedias()
    {
        /** @var PersistentCollection $collection */
        $collection = parent::getGalleryHasMedias();

        //hack: avoid exception caused by softDeleted media
        /** @var \Application\MediaBundle\Entity\GalleryHasMedia $galleryHasMedia */
        foreach ($collection as $key => $galleryHasMedia) {
            if (null === $galleryHasMedia->getMedia()) {
                continue;
            }

            $deleted = null;
            try {
                /** @var Media $media */
                $media = $galleryHasMedia->getMedia();
                $deleted = $media->getDeletedAt();
            } catch (EntityNotFoundException $e) {
                $deleted = true;
            }

            if (null !== $deleted) {
                $collection->remove($key);
            }
        }

        return $collection;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    private function autoSetPrimaryMedia()
    {
        return;

        if (null === $this->primaryMedia) {
            $galleryHasMedia = $this->getGalleryHasMedias()->first();
            if ($galleryHasMedia instanceof \Application\MediaBundle\Entity\GalleryHasMedia && $galleryHasMedia->getMedia()) {
                $this->setPrimaryMedia($galleryHasMedia->getMedia());
            }
        }
    }

    public function preUpdate()
    {
        parent::preUpdate();
        $this->autoSetPrimaryMedia();
    }

    public function prePersist()
    {
        parent::prePersist();
        $this->autoSetPrimaryMedia();
    }

    public function setGalleryHasMedias($newGalleryHasMedias)
    {
        $compareGHM = function (GalleryHasMediaInterface $a, GalleryHasMediaInterface $b) {
            return $a->getMedia() && $b->getMedia()
                && $a->getGallery() && $b->getGallery()
                && $a->getMedia()->getId() === $b->getMedia()->getId()
                && $a->getGallery()->getId() === $b->getGallery()->getId();
        };

        /** @var GalleryHasMedia $newGHM */
        foreach ($newGalleryHasMedias as $newGHM) {
            $newGHM->setGallery($this);

            $alreadyExists = false;
            foreach ($this->galleryHasMedias as $storedGHM) {
                if ($compareGHM($newGHM, $storedGHM)) {
                    $alreadyExists = true;
                    break;
                }
            }

            if (!$alreadyExists) {
                $this->galleryHasMedias->add($newGHM);
            }
        }

        foreach ($this->galleryHasMedias as $storedGHM) {
            $stillNeeded = false;
            foreach ($newGalleryHasMedias as $newGHM) {
                if ($compareGHM($newGHM, $storedGHM)) {
                    $stillNeeded = true;
                    break;
                }
            }
            if (!$stillNeeded) {
                $this->galleryHasMedias->removeElement($storedGHM);
            }
        }
    }

    public function getMedia()
    {
        return $this
            ->getGalleryHasMedias()
            ->filter(function(GalleryHasMedia $galleryHasMedia) {
                return $galleryHasMedia->getEnabled() && $galleryHasMedia->getMedia()->getEnabled();
            })
            ->map(function (GalleryHasMedia $galleryHasMedia) {
                if ($galleryHasMedia->getEnabled()) {

                }
                return $galleryHasMedia->getMedia();
            });
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function mainMedia()
    {
        if ($this->getPrimaryMedia()) {
            return $this->getPrimaryMedia();
        }

        foreach ($this->getGalleryHasMedias() as $ghm) {
            if ($ghm->getEnabled() && $ghm->getMedia() && $ghm->getMedia()->getEnabled()) {
                return $ghm->getMedia();
            };
        };

        return null;
    }
}
