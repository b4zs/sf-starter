<?php

namespace Core\LoggableEntityBundle\Block;

use Core\LoggableEntityBundle\Admin\Extension\LoggableEntityExtension;
use Core\LoggableEntityBundle\Service\EntityChangeLogService;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManagerInterface;
use Sonata\BlockBundle\Block\BaseBlockService;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class EntityLogBlockService extends BaseBlockService
{
	/**
	 * @var EntityChangeLogService
	 */
	protected $loggableService;

	/**
	 * @var EntityManagerInterface
	 */
	protected $entityManager;

	/**
	 * @param BlockContextInterface $blockContext
	 * @param Response|null $response
	 * @return Response
	 */
	public function execute(BlockContextInterface $blockContext, Response $response = null)
	{
		$className = ClassUtils::getRealClass($blockContext->getSetting('subject_class'));
		$id = $blockContext->getSetting('subject_id');

		$entries =  $this
			->loggableService
			->getLogEntries($this->entityManager, $className, $id, true);

		return $this->renderResponse($blockContext->getTemplate(), array(
			'block_context'  => $blockContext,
			'block'          => $blockContext->getBlock(),
			'entries'        => $entries,
		), $response);
	}


	public function setDefaultSettings(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
			'template'      => 'LoggableEntityBundle:Block:entityLogBlock.html.twig',
			'subject_class' => null,
			'subject_id'    => null,
		));
	}

	/**
	 * @param EntityChangeLogService $loggableService
	 */
	public function setLoggableService($loggableService)
	{
		$this->loggableService = $loggableService;
	}

	/**
	 * @param EntityManagerInterface $em
	 */
	public function setEntityManager(EntityManagerInterface $em) {
		$this->entityManager = $em;
	}
}