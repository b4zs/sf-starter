<?php


namespace Core\MediaBundle\Admin\ORM;


use Core\ObjectIdentityBundle\Model\ObjectIdentityAware;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Knp\Menu\ItemInterface as MenuItemInterface;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelListType;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\Form\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

/**
 * @property mixed getParentObjectIdentity
 */
class GalleryAdmin extends \Sonata\MediaBundle\Admin\GalleryAdmin
{

    protected function configureFormFields(FormMapper $formMapper)
    {
        parent::configureFormFields($formMapper);

        $context = $this->getPersistentParameter('context');

        if (!$context) {
            $context = $this->pool->getDefaultContext();
        }

        $contexts = [];
        foreach ((array) $this->pool->getContexts() as $contextItem => $format) {
            $contexts[$contextItem] = $contextItem;
        }

        $formMapper
            ->end()
            ->with('Options', ['label' => false,'translation_domain' => $this->translationDomain])
                ->remove('context')
                ->remove('enabled')
//                ->remove('name')
                ->add('name', null, ['required' => true,], ['translation_domain' => $this->translationDomain])
                ->add('primaryMedia', ModelListType::class, array('required' => false))
                ->add('context', ChoiceType::class, ['choices' => $contexts])
                ->add('enabled', null, ['required' => false])
            ->end();

        $formMapper
            ->with('Gallery')
                ->add('galleryHasMedias', CollectionType::class, ['by_reference' => false], [
                    'edit' => 'inline',
                    'label' => false,
                    'inline' => 'table',
                    'sortable' => 'position',
                    'link_parameters' => ['context' => $context],
                    'admin_code' => 'sonata.media.admin.gallery_has_media',
                ])
            ->end();

        $formMapper
            ->with('Description', ['class' => 'col-md-9'])
            ->add('description', CKEditorType::class, array('required' => false, 'label' => false,))
            ->end();
    }

    public function createQuery($context = 'list')
    {
        $query = parent::createQuery($context);

        if ($parentObjectIdentity = $this->getParentSubjectObjectIdentity()) {
            $rootAlias = $query->getRootAliases();
            $rootAlias = array_shift($rootAlias);

            $query
                ->andWhere(sprintf('%s.owner = :gallery_owner', $rootAlias))
                ->setParameter('gallery_owner', $parentObjectIdentity);
        }
        return $query;
    }

    public function getNewInstance()
    {
        $gallery = parent::getNewInstance();

        if ($parentObjectIdentity = $this->getParentSubjectObjectIdentity()) {
            /** @var Gallery $gallery */
            $gallery = parent::getNewInstance();
            $gallery->setOwner($parentObjectIdentity);
        }

        return $gallery;
    }

    private function getParentSubjectObjectIdentity()
    {
        return ($this->isChild() && $this->getParent() instanceof Admin && $this->getParent()->getSubject() instanceof ObjectIdentityAware)
            ? $this->getParent()->getSubject()->getObjectIdentity()
            : null;

    }

    protected function configureSideMenu(MenuItemInterface $menu, $action, AdminInterface $childAdmin = null)
    {
        if ($this->getSubject() && $this->getSubject()->getId() && $this->hasChildren()) {
            $children = $this->getChildren();

            $admin = $this->isChild() ? $this->getParent() : $this;
            $id = $admin->getRequest()->get('id');

            foreach ($children as $childAdmin) {
                /** @var $childAdmin Admin */
                if ($childAdmin->isGranted('LIST')) {
                    $menu->addChild(
                        $this->trans('sidemenu.link_list_' . $childAdmin->getClassnameLabel()),
                        array('uri' => $childAdmin->generateUrl('list', array('id' => $id)))
                    );
                }
            }
        }
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        parent::configureListFields($listMapper);
    }

    public function getNormalizedIdentifier($entity)
    {
        return $entity ? parent::getNormalizedIdentifier($entity) : $entity;
    }

    public function getLabel()
    {
        return 'admin_label.gallery';
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->add('multiupload', $this->getRouterIdParameter().'/multiupload');
    }

    public function configureActionButtons($action, $object = null)
    {
        $buttons = parent::configureActionButtons($action, $object);
        if ($object && $object->getId() && class_exists('Core\\ChunkUploadBundle\\Form\\ChunkUploadCollectionType')) {
            if ($action === 'multiupload') {
                $buttons['edit'] = ['template' => $this->getTemplate('button_edit'),];
            } else {
                $buttons[] = [
                    'template' => 'CoreMediaBundle:GalleryAdmin:multiupload_button.html.twig',
                ];
            }
        }

        return $buttons;
    }
}
