<?php


namespace Core\BlockBundle\Block;


use Core\PageBundle\Entity\Page;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class BlockContextManager extends \Sonata\PageBundle\Block\BlockContextManager
{
	/** @var  ContainerInterface */
	private $container;

	/**
	 * @param ContainerInterface $container
	 */
	public function setContainer($container)
	{
		$this->container = $container;
	}

	protected function setDefaultExtraCacheKeys(BlockContextInterface $blockContext, array $settings)
	{
		$settings = $blockContext->getSettings();
		if (!empty($settings['use_cache'])) {
			$securityContext = $this->container->get('security.context');
			$isAdmin = $securityContext->getToken() && $securityContext->isGranted('ROLE_SONATA_ADMIN');
			$user = $isAdmin ? $securityContext->getToken()->getUser() : null;
			if (null !== $user) {
				$sonataPage = $this->container->get('sonata.page.twig.global');
				/** @var Page $page */
				$page = $sonataPage->isInlineEditionOn() && $sonataPage->isEditor() ? $sonataPage->getCmsManager()->getCurrentPage() : null;
				$editing = $page && $page->getActiveEditor() && $page->getActiveEditor()->getId() == $user->getId();

				if ($editing) {
					$blockContext->setSetting('use_cache', false);
				}
			}
		}

		return parent::setDefaultExtraCacheKeys($blockContext, $settings);
	}

} 