<?php


namespace Core\MediaBundle\Form\DataTransformer;


use Application\MediaBundle\Entity\GalleryHasMedia;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class GalleryHasMediasDataTransformer implements DataTransformerInterface
{
	/**
	 * @var \Doctrine\ORM\EntityManager
	 */
	private $entityManager;

	public function __construct(EntityManager $entityManager)
	{

		$this->entityManager = $entityManager;
	}

	public function transform($value)
	{
		if ($value instanceof ArrayCollection) {
			$result = array();
			/** @var GalleryHasMedia $galleryHasMedia */
			foreach ($value as $galleryHasMedia) {
				if ($galleryHasMedia->getGallery() && $galleryHasMedia->getGallery()->getId()) {
					$result[] = $galleryHasMedia->getGallery()->getId();
				}
			}

			return $result;
		};

		return $value;
	}


	public function reverseTransform($value)
	{
		if (is_array($value)) {
			/** @var EntityRepository $relationRepository */
			$relationRepository = $this->entityManager->getRepository('ApplicationMediaBundle:GalleryHasMedia');

			/** @var EntityRepository $galleryRepository */
			$galleryRepository = $this->entityManager->getRepository('ApplicationMediaBundle:Gallery');

			$result = array();
			foreach ($value as $galleryId) {
				$gallery = $galleryRepository->find($galleryId);
				if (null === $gallery) {
					throw new TransformationFailedException(sprintf('Gallery not found by id: %d', $galleryId));
				}

				$galleryHasMedia = new GalleryHasMedia();
				$galleryHasMedia->setGallery($gallery);
				$result[] = $galleryHasMedia;
			}

			return new ArrayCollection($result);
		}

		return $value;
	}
}