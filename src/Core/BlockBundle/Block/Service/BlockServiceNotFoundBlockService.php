<?php


namespace Core\BlockBundle\Block\Service;


use Core\PageBundle\Entity\Block;
use Core\PageBundle\Entity\Page;
use Sonata\BlockBundle\Block\BaseBlockService;
use Sonata\BlockBundle\Block\BlockContext;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Symfony\Component\HttpFoundation\Response;

class BlockServiceNotFoundBlockService extends BaseBlockService
{
	/** @var \Exception */
	private $exception;


	/**
	 * @param \Exception $exception
	 */
	public function setException($exception)
	{
		$this->exception = $exception;
	}

	public function execute(BlockContextInterface $blockContext, Response $response = null)
	{
		$text = $this->exception->getMessage();

//		$block = new Block();
//		$block->setPage(new Page());
//		$blockContext = new BlockContext($block, $blockContext->getSettings());
		$body = $this->templating->render('CorePageBundle:Block:block_base.html.twig', array(
			'block_context'  => $blockContext,
			'block'          => $blockContext->getBlock(),
			'content'        => $text,
		));
		$response->setContent($body);

		return $response;
	}


} 