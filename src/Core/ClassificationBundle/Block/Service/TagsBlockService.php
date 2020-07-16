<?php


namespace Core\ClassificationBundle\Block\Service;


use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\ClassificationBundle\Admin\TagAdmin;
use Sonata\ClassificationBundle\Block\Service\AbstractClassificationBlockService;
use Sonata\ClassificationBundle\Block\Service\AbstractTagsBlockService;
use Sonata\ClassificationBundle\Model\ContextManagerInterface;
use Sonata\ClassificationBundle\Model\TagManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Templating\EngineInterface;

class TagsBlockService extends AbstractClassificationBlockService
{
    /**
     * @var TagManagerInterface
     */
    private $tagManager;
    /**
     * @var TagAdmin
     */
    private $tagAdmin;

    public function __construct($name, EngineInterface $twig, ContextManagerInterface $contextManager, TagManagerInterface $tagManager, TagAdmin $tagAdmin)
    {
        parent::__construct($name, $twig, $contextManager);
        $this->tagManager = $tagManager;
        $this->tagAdmin = $tagAdmin;
    }

    public function configureSettings(OptionsResolver $resolver)
    {
        parent::configureSettings($resolver);
        $resolver->setDefault('current_slug', null);
        $resolver->setDefault('filter_context', null);
    }


    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {
        $context = $blockContext->getSetting('filter_context');
        $currentSlug = $blockContext->getSetting('current_slug');

        $tags = $this->tagManager->getByContext($context);
        $current = $this->tagManager->findOneBy([
            'context' => $context,
            'slug' => $currentSlug,
        ]);

        return $this->renderResponse($blockContext->getTemplate(), [
            'context' => $blockContext,
            'settings' => $blockContext->getSettings(),
            'block' => $blockContext->getBlock(),
            'tags' => $tags,
            'current' => $current,
        ], $response);
    }

}
