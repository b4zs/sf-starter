<?php
namespace Core\MediaBundle\Form\DataTransformer;

use Application\MediaBundle\Entity\Media;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Provider\Pool;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Doctrine\Common\Collections\ArrayCollection;
use Sonata\CoreBundle\Model\ManagerInterface;

/**
 * Class MediaCollectionDataTransformer
 * @package Core\MediaBundle\Form\DataTransformer
 */
class MediaCollectionDataTransformer implements DataTransformerInterface
{

    /**
     * @var bool
     */
    protected $isDebug;

    /**
     * @var ManagerInterface
     */
    protected $mediaManager;

    /**
     * @var Pool
     */
    protected $pool;

    /**
     * @param ManagerInterface $manager
     * @param Pool $pool
     * @param bool|false $isDebug
     */
    public function __construct(ManagerInterface $manager, Pool $pool, $isDebug = false ) {

        $this->mediaManager = $manager;
        $this->pool = $pool;
        $this->isDebug = $isDebug;
    }

    /**
     * {@inheritdoc}
     * @param mixed $value
     * @return array|mixed
     */
    public function transform($value)
    {
        if(null === $value) {
            return null;
        }

        $ret = array();

        if(!is_array($value)) {
            if($this->isDebug) {
                throw new TransformationFailedException(sprintf("Transformation failed in [%s], the value is not an array!", __CLASS__));
            }
            return $ret;
        }

        foreach($value as $media) {
            /** @var MediaInterface $media */
            if($media instanceof Media) {
                $ret[] = $media->getId();
            }
        }

        return $ret;
    }

    /**
     * {@inheritdoc}
     * @param mixed $value
     * @return mixed
     */
    public function reverseTransform($value)
    {
        if(null === $value) {
            return null;
        }

        $ret = array();

        if(!is_array($value)) {
            if($this->isDebug) {
                throw new TransformationFailedException(sprintf("Reverse transformation failed in [%s], the value is not an array!", __CLASS__));
            }
            return $ret;
        }

        foreach($value as $media) {
            $ret[] = $this->mediaManager->find($media);
        }

        return $ret;
    }
}