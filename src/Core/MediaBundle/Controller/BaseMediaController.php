<?php


namespace Core\MediaBundle\Controller;


use Application\MediaBundle\Entity\Media;
use Doctrine\ORM\EntityRepository;
use FOS\Rest\Util\Codes;
use FOS\RestBundle\Controller\FOSRestController;
use Core\MediaBundle\Form\Filter\MediaApiFilterType;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class BaseMediaController extends FOSRestController
{

	/**
	 * @return EntityRepository
	 */
	protected function getRepository()
	{
		return $this->container->get('doctrine')->getRepository('ApplicationMediaBundle:Media');
	}

	/**
	 * @param $mediaId
	 * @param string $permissionToCheck
	 * @return \Application\MediaBundle\Entity\Media
	 */
	protected function loadMedia($mediaId, $permissionToCheck = 'VIEW')
	{
		$media = $this->getRepository()->find($mediaId);
		if (null === $media) {
			throw new HttpException(404, sprintf('Media not found by id: %d', $mediaId));
		}

		if ($permissionToCheck && !$this->container->get('security.context')->isGranted($permissionToCheck, $media)) {
			$translator = $this->container->get('translator');
			var_dump($translator->trans(sprintf('message.%s_not_granted', strtolower($permissionToCheck))));die;
			throw new HttpException(Codes::HTTP_FORBIDDEN, $translator->trans(sprintf('message.%s_not_granted', strtolower($permissionToCheck))));
		}

		return $media;
	}

	/**
	 * @return \Doctrine\ORM\QueryBuilder
	 */
	protected function createMediaQuery()
	{
		$queryBuilder = $this
			->getRepository()
			->createQueryBuilder('media')
			->orderBy('media.id', 'DESC');
		$filterFormType = new MediaApiFilterType();
		$filterForm = $this->createForm($filterFormType, array());
		$filterForm->handleRequest($this->container->get('request'));
		if ($filterForm->isSubmitted() && $filterForm->isValid()) {
			$filterFormType->filterQuery($queryBuilder, $filterForm->getData());
			return $queryBuilder;
		}
		return $queryBuilder;
	}

	/**
	 * @param $queryBuilder
	 * @return \FOS\RestBundle\View\View
	 */
	protected function buildMediaQueryResponse($queryBuilder)
	{
		$request = $this->container->get('request');
		$mediaBuilder = $this->container->get('core.media.model_builder.media');
		$mediaBuilderOptions = array(
			'parts' => $request->get('parts', array('base')),
		);

		return $this->container->get('core_tools.api.list_provider')
			->setQuery($queryBuilder)
			->setRequest($this->container->get('request'))
			->setClosure(function (Media $media) use ($mediaBuilder, $mediaBuilderOptions) {
				return $mediaBuilder->build($media, $mediaBuilderOptions);
			})
			->createViewResponse(200);
	}

	protected function getGalleryRepository()
	{
		return $this->container->get('doctrine')->getRepository('ApplicationMediaBundle:Gallery');
	}

	protected function loadGallery($galleryId, $permissionToCheck = 'VIEW')
	{
		$record = $this->getGalleryRepository()->find($galleryId);
		if (null === $record) {
			throw new HttpException(404, 'Gallery not found by id: ' . $galleryId);
		}

		if ($permissionToCheck && !$this->container->get('security.context')->isGranted($permissionToCheck, $record)) {
			$translator = $this->container->get('translator');
			throw new AccessDeniedHttpException($translator->trans(sprintf('message.%s_not_granted', strtolower($permissionToCheck))));
		}

		return $record;
	}
}