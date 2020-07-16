<?php


namespace Core\ChunkUploadBundle\Form\DataTransformer;


use Doctrine\ORM\EntityManager;
use Core\ChunkUploadBundle\Builder\MediaDataBuilder;
use Core\ChunkUploadBundle\Model\MediaInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class AsymmetricIdToMediaTransformer implements DataTransformerInterface
{

    /** @var \Doctrine\ORM\EntityManager */
    protected $entityManager;

    /** @var string */
    protected $className;

    /** @var MediaDataBuilder */
    protected $mediaDataBuilder;

    function __construct(EntityManager $entityManager, $className, MediaDataBuilder $mediaDataBuilder)
    {
        $this->entityManager = $entityManager;
        $this->className = $className;
        $this->mediaDataBuilder = $mediaDataBuilder;
    }

    public function transform($object)
    {
        if ($object instanceof MediaInterface) {
            return $object->getId();
        } elseif (null === $object) {
            return $object;
        } else {
            throw $this->unableToProcessType($object);
        }
    }

    public function reverseTransform($object)
    {
        $resultObject = null;
        if (null === $object) {
            $object = '';
        }

        if (!$object) {
            return null;
        }

        if (is_array($object) && array_key_exists('id', $object)) {
            $object = $object['id'];
        }

        if (is_string($object) || is_numeric($object)) {
            /** @var MediaInterface $resultObject */
            $resultObject = $this->entityManager->getRepository($this->className)->find($object);
            $resultObject->setIsTmp(false);
        }

        if (is_object($resultObject)) {
            return $resultObject;
        } else {
            throw $this->unableToProcessType($object);
        }
    }

    protected function unableToProcessType($value)
    {
        switch (true) {
            case is_object($value):
                $type = get_class($value);
                break;
            case is_array($value):
                $type = 'array';
                break;
            case is_string($value):
                $type = 'string';
                break;
            case is_numeric($value):
                $type = 'numeric';
                break;
            case null === $value:
                $type = 'NULL';
                break;
            default:
                $type = '??';
        }

        return new TransformationFailedException('Unable to process object with type ' . $type);
    }

    public function buildMediaById($mediaId)
    {
        $media = $this->entityManager->getRepository($this->className)->find($mediaId);
        return $this->mediaDataBuilder->buildData($media);
    }
}
