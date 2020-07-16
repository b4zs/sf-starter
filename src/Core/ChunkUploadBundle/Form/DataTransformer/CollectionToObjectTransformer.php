<?php
namespace Core\ChunkUploadBundle\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\Common\Collections\ArrayCollection;
use Sonata\MediaBundle\Model\MediaInterface;

class CollectionToObjectTransformer implements DataTransformerInterface
{

    public function transform($value)
    {
        if(!$value instanceof MediaInterface){
            return $value;
        }

        return array(array($value));
    }

    public function reverseTransform($value)
    {
        if(!$value instanceof \Traversable && !is_array($value)){
            return $value;
        }

        $val = current($value);

        return false === $val ? null : $val;
    }

}
