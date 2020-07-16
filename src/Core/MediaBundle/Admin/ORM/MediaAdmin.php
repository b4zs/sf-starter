<?php


namespace Core\MediaBundle\Admin\ORM;

use Application\MediaBundle\Entity\GalleryHasMedia;
use Core\MediaBundle\Entity\Media;
use Core\PageBundle\Entity\Site;
use Core\Sonata\ClassificationBundle\Entity\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Knp\Menu\ItemInterface as MenuItemInterface;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\ClassificationBundle\Model\TagInterface;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Sonata\MediaBundle\Admin\ORM\MediaAdmin as BaseMediaAdmin;
use Sonata\MediaBundle\Model\GalleryInterface;
use Sonata\MediaBundle\Provider\MediaProviderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class MediaAdmin extends BaseMediaAdmin
{
    /** @var  EntityManager */
    private $entityManager;

    /**
     * @param mixed $entityManager
     */
    public function setEntityManager($entityManager)
    {
        $this->entityManager = $entityManager;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        parent::configureDatagridFilters($datagridMapper);
        $datagridMapper->remove('providerReference');
        $datagridMapper->remove('contentType');

        if (class_exists('Core\ClassificationBundle\Entity\Tag')) {
            $datagridMapper
                ->add(
                    'tags',
                    'doctrine_orm_callback',
                    array(
                        'callback' => array($this, 'filterByTags'),
                    ),
                    EntityType::class,
                    array(
                        'multiple' => true,
                        'expanded' => false,
                        'required' => false,
                        'multiple' => true,
                        'class' => 'Core\ClassificationBundle\Entity\Tag',
                    )
                );
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function configureShowFields(ShowMapper $filter)
    {
        $container = $this->configurationPool->getContainer();
        $router = $container->get('router');
        $filter
            ->add('id', null, array(
                'label' => 'id',
            ))
            ->add('name')
            ->add('description');

        if (class_exists('Core\ClassificationBundle\Entity\Tag')) {
            $filter->add('tags');
        }

        $filter
            ->add('url', null, array(
                'template' => 'CoreAdminBundle:List:callback_field.html.twig',
                'raw' => true,
                'callback' => function ($fieldValue, $object) use ($router) {

                    $url = $router->generate('sonata_media_download', array('id' => $object->getId()));
                    return sprintf('<td><a href="%s">%s</a></td>', $url, $url);
                }
            ));
        if (class_exists('Core\ClassificationBundle\Entity\Collection')) {
            $filter->add('collection');
        }

        $filter
            ->add('createdAt');
    }


    public function filterByTags(ProxyQuery $query, $alias, $field, $value)
    {
        if (!is_array($value) || !isset($value['value'])) {
            return;
        } else {
            /** @var ArrayCollection $value */
            $value = $value['value'];
            $value = $value->map(function (TagInterface $tag) {
                return $tag->getId();
            })->getValues();

            if (empty($value)) {
                return;
            }
        }

        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = $query->getQueryBuilder();
        $rootAliases = $queryBuilder->getRootAliases();
        $rootAlias = current($rootAliases);


        $queryBuilder
            ->innerJoin(sprintf('%s.tags', $rootAlias), 'tag')
            ->andWhere('tag.id IN (:tag_ids)')
            ->setParameter('tag_ids', $value);

        return true;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('name', 'string', array('template' => 'CoreMediaBundle:MediaAdmin:list_custom.html.twig', 'label' => 'list.label_name'))
            ->add('enabled', 'boolean', array('editable' => true))
            ->add('_action', 'actions', array(
                'actions' => array(
                    'show' => array(),
                    'edit' => array(),
                    'delete' => array(),
                )
            ));
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper->with($this->trans('File data'), array('class' => 'col-md-6'));
        parent::configureFormFields($formMapper);
        $formMapper->end();

        $formMapper->with($this->trans('Options'), array('class' => 'col-md-6'));

        if (class_exists('Core\ClassificationBundle\Entity\Tag')) {
            $formMapper
                ->add('tags', EntityType::class, array(
                    'multiple' => true,
                    'label' => 'form.label_tags',
                    'class' => 'Core\ClassificationBundle\Entity\Tag',
                    'required' => false,
                    'attr' => array(
                        'style' => 'width:100%',
                    )
                ));
        }

        $formMapper->end();

        $subject = $this->getSubject();
    }

    public function configure()
    {
        parent::configure();

        $this->datagridValues['_sort_by'] = 'id';
        $this->datagridValues['_sort_order'] = 'DESC';
    }

    public function isGranted($name, $object = null)
    {
        return parent::isGranted($name, $object);
    }

    public function getTemplate($name)
    {
        switch ($name) {
            case 'edit':
                return 'CoreMediaBundle:MediaAdmin:edit.html.twig';
            case 'browser_edit':
                return 'CoreMediaBundle:MediaAdmin:browser_edit.html.twig';
            default:
                return parent::getTemplate($name);
        }
    }

    public function getNewInstance()
    {
        /** @var Media $media */
        $media = parent::getNewInstance();

        if ($gallery = $this->getGalleryFromParentAdmin()) {
            $galleryHasMedia = new GalleryHasMedia();
            $galleryHasMedia->setGallery($gallery);
            $galleryHasMedia->setMedia($media);

            $galleryHasMedias = $media->getGalleryHasMedias();
            $galleryHasMedias->add($galleryHasMedia);
            $media->setGalleryHasMedias($galleryHasMedias);

        }


        if ($this->hasRequest() && ($tagIdentifier = $this->getRequest()->get('tag'))) {
            $tagRepository = $this->entityManager->getRepository('CoreClassificationBundle:Tag');
            if (is_numeric($tagIdentifier)) {
                $tag = $tagRepository->findOneBy(array('id' => $tagIdentifier));
            } elseif (is_string($tagIdentifier)) {
                $tag = $tagRepository->findOneBy(array('name' => $tagIdentifier));
                if (!$tag) {
                    $tag = new Tag();
                    $tag->setName($tagIdentifier);
                    $tag->setEnabled(true);
                    $this->entityManager->persist($tag);
                }
            }

            if ($tag) {
                $media->getTags()->add($tag);
            }
        }

        return $media;
    }

    private function getGalleryFromParentAdmin()
    {
        return $this->isChild() && $this->getParent() && $this->getParent()->getSubject() instanceof GalleryInterface
            ? $this->getParent()->getSubject()
            : null;
    }

    public function createQuery($context = 'list')
    {
        $query = parent::createQuery($context);

        if ($gallery = $this->getGalleryFromParentAdmin()) {
            $rootAlias = $query->getRootAliases();
            $rootAlias = array_shift($rootAlias);

            $query
                ->innerJoin(sprintf('%s.galleryHasMedias', $rootAlias), 'galleryHasMedias')
                ->andWhere('galleryHasMedias.media = :medias_gallery')
                ->setParameter('medias_gallery', $gallery);
        }


        return $query;
    }

    public function toString($object)
    {
        if (!$object->getId()) {
            return $this->trans('label.create_new');
        } else {
            return parent::toString($object);
        }
    }

    protected function configureTabMenu(MenuItemInterface $menu, $action, AdminInterface $childAdmin = null)
    {
        if ('create' === $action) {
            $providers = $this->pool->getProvidersByContext($this->getRequest()->get('context', $this->pool->getDefaultContext()));
            /** @var MediaProviderInterface $provider */
            foreach ($providers as $provider) {
                $url = $this->generateUrl('create', array('provider' => $provider->getName()));
                $name = $provider->getName();
                $menu->addChild($this->trans($name), array('uri' => $url));
            }

        }
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->add('browser');
        $collection->add('upload');
    }

    public function getPersistentParameters()
    {
        $parameters = parent::getPersistentParameters();

        foreach (array('CKEditor') as $key) {
            if ($this->getRequest()->query->has($key)) {
                $parameters[$key] = $this->getRequest()->query->get($key);
            }
        }

        return $parameters;
    }

    public function getLabel()
    {
        return 'admin_label.media';
    }


}
