<?php


namespace Core\BlockBundle\Block\Service;

use Core\AdminBundle\Form\AdminFormHelper;
use Core\MenuBundle\Entity\MenuNode;
use Core\MenuBundle\Form\DataTransformer\JsonDataTransformer;
use Core\MenuBundle\Form\DataTransformer\MenuNodeTreeDataTransformer;
use Core\PageBundle\Block\ThemedTemplateSelectorAware;
use Core\PageBundle\Entity\Block;
use Core\PageBundle\Entity\Page;
use Core\PageBundle\Entity\Site;
use Core\PageBundle\Theming\ThemeHelper;
use Knp\Menu\Provider\MenuProviderInterface;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Validator\ErrorElement;
use Sonata\BlockBundle\Block\BaseBlockService;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Block\Service\MenuBlockService as BaseMenuBlockService;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\PageBundle\Model\SnapshotPageProxy;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Finder\Tests\Iterator\Iterator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\CallbackValidator;

class MenuBlockService extends BaseMenuBlockService
{
    use ThemedTemplateSelectorAware;

    const SERVICE_ID = 'core.block.service.menu';

	/** @var  AdminFormHelper */
	protected $adminFormHelper;

    public function __construct($name, EngineInterface $templating, MenuProviderInterface $menuProvider, $menus)
    {
        parent::__construct($name, $templating, $menuProvider, $menus);

        $this->menuProvider = $menuProvider;
        $this->menus        = $menus;
    }

	public function buildEditForm(FormMapper $form, BlockInterface $block)
	{
		$form->add('settings', 'sonata_type_immutable_array', array(
			'cascade_validation'    => true,
			'keys'                  => $this->getFormSettingsKeysForBlock($block, $form),
		));
	}

	protected function getFormSettingsKeysForBlock(BlockInterface $block, FormMapper $formMapper)
    {
        $settingsKeys = array();
	    $menuNode = null;

	    if (($menuName = $block->getSetting('menu_name')) && null !== $this->adminFormHelper) {
			/** @var MenuNode $menuNode */
            $menuNode = $this->loadMenuNode($menuName);
            if (null !== $menuNode) {
			    $block->setSetting('menu_node', $menuNode->getChildren());
			    $childrenField = $formMapper->create('menu_node', 'menu_node_tree', array('label' => 'Items', 'node' => $block));

			    $childrenField
				    ->addViewTransformer(new MenuNodeTreeDataTransformer(
					    $this->container->get('doctrine.orm.entity_manager'),
					    $this->container->get('core.cms.menu.menu_list_provider'),
					    $this->container->get('sonata.admin.pool'),
					    $this->container->get('translator'),
					    $menuNode
				    ))
				    ->addViewTransformer(new JsonDataTransformer());

			    $settingsKeys[] = array($childrenField, null, array());
		    }
	    }

	    if (null === $menuNode) {
		    $choices = array('' => '-create new-') + $this->menus->getMenuChoices();
	        $settingsKeys[] = array('menu_name', 'choice', array('choices' => $choices, 'required' => false, 'attr' => array('style' => 'width: 100%;')));
//	        $settingsKeys[] = array('use_menu_of_current_page', 'checkbox', array('required' => false, 'label' => 'form.label_use_menu_of_current_page'));
	    }

	    $settingsKeys[] = array('menu_template', 'choice', array('choices' => $this->getTemplateChoices($block), 'required' => false, 'translation_domain' => 'messages', 'attr' => array('style' => 'width: 100%;')));
	    $settingsKeys[] = array('max_levels', 'number', array('required' => false));
	    $settingsKeys[] = array('current_class', 'text', array('required' => false));
	    $settingsKeys[] = array('menu_class', 'text', array('required' => false));
	    $settingsKeys[] = array('children_class', 'text', array('required' => false));
	    $settingsKeys[] = array('first_class', 'text', array('required' => false));
	    $settingsKeys[] = array('last_class', 'text', array('required' => false));
	    $settingsKeys[] = array('safe_labels', 'checkbox', array('required' => false));
//	    $settingsKeys[] = array('cache_policy', 'choice', array('choices' => array('public', 'private')));
	    $settingsKeys[] = array('use_cache', 'checkbox', array('required' => false));

        return $settingsKeys;
    }

	public function execute(BlockContextInterface $blockContext, Response $response = null)
	{
		$options = $this->getMenuOptions($blockContext->getBlock()->getSettings());
		$template = $blockContext->getTemplate(); // DO NOT use the menu_template: blockTpl is the wrapper div, menuTpl is for KNP menu

		$menu = $this->getMenu($blockContext);
		if (!$this->menuProvider->has($menu)) {
			throw new HttpException(400, sprintf('The menu "%s" is not defined', $menu));
		}

		$responseSettings = array(
			'menu'         => $this->container->get('knp_menu.menu_provider')->get($menu, $options),
			'menu_options' => $options,
			'block'        => $blockContext->getBlock(),
			'context'      => $blockContext
		);

		if ('private' === $blockContext->getSettings('cache_policy')) {
			return $this->renderPrivateResponse($template, $responseSettings);
		} else {
			return $this->renderResponse($template, $responseSettings);
		}
	}

    public function configureSettings(OptionsResolver $resolver)
    {
        parent::configureSettings($resolver);

        $resolver->setDefaults(array(
            'use_cache'             => false,
            '_admin_reopen_form'    => false,
	        'menu_template'         => $this->getDefaultTemplate(),
	        'max_levels'            => 1,
	        'menu_node'             => null,
	        'menu_name'             => null,
	        'current_class'         => null,
	        'first_class'           => null,
	        'last_class'            => null,
	        'safe_labels'           => null,
	        'title'                 => null,
        ));
    }

    public function addTemplate($twigName, $name)
    {
        $this->templates[$twigName] = $name;
    }

    /**
     * Replaces setting keys with knp menu item options keys
     *
     * @param array $settings
     * @return array
     */
    protected function getMenuOptions(array $settings)
    {
        //block settings -> menu_options mapping
        $mapping = array(
            'current_class' => 'currentClass',
            'first_class'   => 'firstClass',
            'last_class'    => 'lastClass',
            'safe_labels'   => 'allow_safe_labels',
            'menu_template' => 'template',
            'title'         => 'title',
            'max_levels'    => 'depth',
            'menu_name'     => 'menu_name',
	        'use_cache'     => 'use_cache',
        );

        $options = array();

        foreach ($settings as $key => $value) {
            if (array_key_exists($key, $mapping)) {
                $options[$mapping[$key]] = $value;
            }
        }

        $options = array_filter($options);

        if (!isset($options['menu_name'])) {
            $options['menu_name'] = null;
        }


        if (!empty($settings['use_menu_of_current_page'])) {
            $currentPage = $this->container->get('sonata.page.cms_manager_selector')->retrieve()->getCurrentPage();
            if ($currentPage instanceof SnapshotPageProxy) {
                $currentPage = $currentPage->getPage();
            }
            if ($currentPage instanceof Page) {
                $menuNode = $this->getMenuByPage($currentPage);
                if ($menuNode) {
                    $options['menu_name'] = $menuNode->getId();
                }
            }
        }

        if (!array_key_exists('fallback_to_parent_if_empty', $options)) {
            $options['fallback_to_parent_if_empty'] = true;
        }

        return $options;
    }

    public function getMenuByPage(Page $page)
    {
        $menu = $this->container->get('doctrine')->getRepository('CoreMenuBundle:MenuNode')->findOneBy(array(
            'page' => $page->getId(),
        ));

        if (!$menu && $page->getContent()) {
            $menu = $this->container->get('doctrine')->getRepository('CoreMenuBundle:MenuNode')->findOneBy(array(
                'content' => $page->getContent()->getId(),
            ));
        }


        return $menu;
    }

	public function preUpdate(BlockInterface $block)
	{
		$this->checkSelectedMenu($block);
		$block->setSetting('_admin_reopen_form', false);
		$block->setSetting('menu_node', null);

	}

	public function prePersist(BlockInterface $block)
	{
		$this->checkSelectedMenu($block);
	}

	public function setAdminFormHelper(AdminFormHelper $adminFormHelper)
	{
		$this->adminFormHelper = $adminFormHelper;
	}

	protected function getMenu(BlockContextInterface $blockContext)
	{
		$block = $blockContext->getBlock();
		$contextValue = parent::getMenu($blockContext);

		return $block ? $block->getSetting('menu_name', $contextValue) : $contextValue;
	}

	/**
	 * @param BlockInterface $block
	 */
	protected function checkSelectedMenu(BlockInterface $block)
	{
		if (!$block->getSetting('menu_name')) {
			$menuNode = new MenuNode();
			$menuNode->setTitle(date('Y-m-d H:i:s'));

			$entityManager = $this->container->get('doctrine.orm.default_entity_manager');
			$entityManager->persist($menuNode);
			$entityManager->flush($menuNode);

			$block->setSetting('menu_name', $menuNode->getId());
			$block->setSetting('_admin_reopen_form', true);
		}
	}

    protected function loadMenuNode($menuName)
    {
        $menuNode = $this->container->get('doctrine.orm.default_entity_manager')->find('CoreMenuBundle:MenuNode', $menuName);
        return $menuNode;
    }


}