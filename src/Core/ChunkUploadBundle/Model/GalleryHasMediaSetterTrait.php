<?php


namespace Core\ChunkUploadBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Sonata\MediaBundle\Model\Gallery;
use Sonata\MediaBundle\Model\GalleryHasMedia;


trait GalleryHasMediaSetterTrait
{
    /**
     * @param Collection|GalleryHasMedia[] $galleryHasMedias
     */
    public function setGalleryHasMedias($galleryHasMedias)
    {
        //DONT ASK
        $newMedias = $galleryHasMedias->map(function(GalleryHasMedia $ghm) {
            return $ghm->getMedia();
        });

        $this->galleryHasMedias->map(function(GalleryHasMedia $ghm) use ($newMedias) {
            $ghm->getMedia()->getGalleryHasMedias()->removeElement($ghm);
            $ghm->setGallery(null);
            if (!$newMedias->contains($ghm->getMedia())) {
                $ghm->setMedia(null);
            }
        });

        Gallery::setGalleryHasMedias($galleryHasMedias);
    }

}
