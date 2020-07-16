<?php


namespace Core\BlockBundle\Block;


use Sonata\BlockBundle\Block\BlockServiceManager as BaseBlockServiceManager;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\PageBundle\Model\PageInterface;

class BlockServiceManager extends BaseBlockServiceManager
{
	private $serviceFilterExecuted = false;


	public function getServices()
	{
		if (false === $this->serviceFilterExecuted) {
			$this->serviceFilterExecuted = true;
			$hiddenBlocks = $this->container->getParameter('sonata.block.hide_services');

			foreach ($this->services as $name => $id) {
				if (in_array($id, $hiddenBlocks)) unset($this->services[$name]);
			}
		}

		$services =  parent::getServices();

		return $services;
	}

	public function get(BlockInterface $block)
	{
		try {
			return parent::get($block);
		} catch(\RuntimeException $e) {
			$block = $this->container->get('core.block.service.block_service_not_found');
			$block->setException($e);
			return $block;
		}
	}




}