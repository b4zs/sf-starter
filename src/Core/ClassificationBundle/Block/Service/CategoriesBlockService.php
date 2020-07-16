<?php


namespace Core\ClassificationBundle\Block\Service;


use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\ClassificationBundle\Block\Service\AbstractCategoriesBlockService;
use Sonata\ClassificationBundle\Model\CategoryInterface;
use Sonata\ClassificationBundle\Model\CategoryManagerInterface;
use Sonata\ClassificationBundle\Model\ContextManagerInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CategoriesBlockService extends AbstractCategoriesBlockService
{
    /** @var CategoryManagerInterface */
    private $categoryManager;

    public function __construct($name, EngineInterface $templating, ContextManagerInterface $contextManager, CategoryManagerInterface $categoryManager, AdminInterface $categoryAdmin)
    {
        parent::__construct($name, $templating, $contextManager, $categoryManager, $categoryAdmin);
        $this->categoryManager = $categoryManager;
    }

    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {
        $category = $this->loadCategoryByParam($blockContext, 'category');
        $current  = $this->loadCategoryByParam($blockContext, 'current');

        $root = $this->categoryManager->getRootCategory($blockContext->getSetting('context'));


        return $this->renderResponse($blockContext->getTemplate(), [
            'context' => $blockContext,
            'settings' => $blockContext->getSettings(),
            'block' => $blockContext->getBlock(),
            'category' => $category,
            'root' => $root,
            'current' => $current,
        ], $response);
    }

    private function loadCategoryByParam(BlockContextInterface $blockContext, string $prefix): ?CategoryInterface
    {
        $id = $blockContext->getSetting($prefix);
        if ($id) {
            return $this->getCategory($id);
        } elseif ($slug = $blockContext->getSetting($prefix.'_slug')) {
            return $this->categoryManager->findOneBy(['slug' => $slug]);
        }

        return null;
    }

    public function configureSettings(OptionsResolver $resolver)
    {
        parent::configureSettings($resolver);

        $resolver->setDefault('current', null);
        $resolver->setDefault('current_slug', null);
        $resolver->setDefault('category_slug', null);
    }
}
