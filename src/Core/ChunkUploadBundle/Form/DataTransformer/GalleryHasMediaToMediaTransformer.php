<?php


namespace Core\ChunkUploadBundle\Form\DataTransformer;


use Application\MediaBundle\Entity\GalleryHasMedia;
use Doctrine\Common\Collections\Collection;
use Core\ChunkUploadBundle\Model\MediaInterface;
use Sonata\MediaBundle\Model\GalleryHasMediaInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\Common\Collections\ArrayCollection;

class GalleryHasMediaToMediaTransformer implements DataTransformerInterface
{
    public function transform($value)
    {
        if ($value instanceof Collection) {
            $value = $value->map(function(GalleryHasMediaInterface $ghm){
                return $ghm->getMedia();
            });
        }

        return $value;
    }

    public function reverseTransform($value)
    {
        if (is_array($value)) {
            $value = new ArrayCollection($value);   
        }
        
        if ($value instanceof Collection) {
            $value = $value->map(function(MediaInterface $m){
                $ghm = new GalleryHasMedia();
                $ghm->setMedia($m);
                return $ghm;
            });
        }

        return $value;
    }
}
