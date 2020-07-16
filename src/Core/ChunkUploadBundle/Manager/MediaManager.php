<?php


namespace Core\ChunkUploadBundle\Manager;


use Doctrine\ORM\EntityManagerInterface;
use Core\ChunkUploadBundle\Model\MediaInterface;

class MediaManager
{

    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function persistMedia(MediaInterface $media)
    {
        $this->entityManager->persist($media);
        $this->entityManager->flush();
    }

}
