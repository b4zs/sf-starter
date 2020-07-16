<?php


namespace Core\MediaBundle\Block;


use Core\AdminBundle\Form\DataTransformer\IdArrayToModelTransformer;
use Core\AdminBundle\Form\DataTransformer\IdToModelTransformer;
use Core\ContentsBundle\Entity\Content;
use Core\ContentsBundle\Entity\ContentId;
use Core\PageBundle\Entity\Page;
use Sonata\AdminBundle\Form\DataTransformer\ArrayToModelTransformer;
use Sonata\AdminBundle\Form\DataTransformer\ModelToIdTransformer;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Validator\ErrorElement;
use Sonata\BlockBundle\Block\BaseBlockService;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\PageBundle\CmsManager\CmsManagerInterface;
use Sonata\PageBundle\Model\SnapshotInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * @deprecated
 */
class MediaListerBlockService extends BaseBlockService
{
    /** @var Container */
    protected $container;

    protected $templates = array();

    /**
     * @param FormMapper $form
     * @param BlockInterface $block
     *
     * @return void
     */
    public function buildEditForm(FormMapper $form, BlockInterface $block)
    {
        $form->add('settings', 'sonata_type_immutable_array', array(
            'keys' => array(
                array('title', 'text', array(
                    'required' => false,
                )),
                $this->getClassificationHelper()->createTagsField($form),
                $this->getClassificationHelper()->createCategoryFields($form),
                $this->createGalleryField($form),
                array('template', 'choice', array(
                    'choices' => $this->getTemplates(),
                    'required' => false,
                )),

            )
        ));
    }

    public function setContainer(Container $container)
    {
        $this->container = $container;
    }

    public function setDefaultSettings(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'title'     => 'Download',
            'tags'      => array(),
            'category'  => null,
            'template'  => $this->getDefaultTemplate(),
            'galleryId' => null,
        ));
    }

    private function getDefaultTemplate()
    {
        $templates = array_keys($this->templates);
        if (!empty($templates)) {
            return reset($templates);
        } else {
            return null;
        }
    }

    public function addTemplate($template, $name)
    {
        $this->templates[$template] = $name;
    }

    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {
        return $this->renderResponse($blockContext->getBlock()->getSetting('template', $this->getDefaultTemplate()), array(
            'block_context'  => $blockContext,
            'block'          => $blockContext->getBlock(),
            'list'           => $this->findMediaBySettings($blockContext->getBlock()->getSettings()),
        ), $response);
    }

    protected function findMediaBySettings($settings)
    {
        $queryBuilder = $this->createMediaQueryBuilder($settings);

        //add tags to query to avoid individual queries towards them during the serialization
        $queryBuilder->select('media as m');
        $queryBuilder->addSelect('tag as t');
        $queryBuilder->leftJoin('media.tags', 'tag');
        $queryBuilder->groupBy('media.id');

        $hasFilter = $this->getClassificationHelper()->addFiltersToQueryBySettings($queryBuilder, $settings, 'media');

        if ($settings['galleryId']) {
            $queryBuilder
                ->innerJoin('media.galleryHasMedias', 'galleryHasMedias')
                ->andWhere('galleryHasMedias.gallery = :galleryId')
                ->andWhere('galleryHasMedias.enabled = true')
                ->setParameter('galleryId', $settings['galleryId'])
                ->orderBy('galleryHasMedias.position', 'ASC');

            $hasFilter = true;
        }

        if ($hasFilter) {
            $resultSet = $queryBuilder->getQuery()->execute();
            $result = array();
            foreach ($resultSet as $record) {
                $result[] = $record['m'];
            }
            return $result;
        } else {
            return array();
        }
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function createMediaQueryBuilder($settings) {
        $queryBuilder = $this->container
            ->get('doctrine.orm.entity_manager')
            ->getRepository($this->container->get('sonata.media.manager.media')->getClass())
            ->createQueryBuilder('media');

        return $queryBuilder;
    }

    protected function getClassificationHelper()
    {
        return $this->container->get('core_classification.block.helper');
    }

    protected function getTemplates()
    {
        $site = $this->container->get('sonata.page.site.selector')->retrieve();

        $baseTemplates = $this->templates;
        $siteTemlpates = $this->container
            ->get('core.page.theming.theme_helper')
            ->getBlockTemplateChoicesOnSite(
                'core.media.block.media_lister',
                $site
            );

        return $baseTemplates + $siteTemlpates;
    }

    protected function createGalleryField($formMapper)
    {
        // simulate an association ...
        $fieldDescription = $this->getGalleryAdmin()->getModelManager()->getNewFieldDescriptionInstance($this->getGalleryAdmin()->getClass(), 'media');
        $fieldDescription->setAssociationAdmin($this->getGalleryAdmin());
        $fieldDescription->setAdmin($formMapper->getAdmin());
        $fieldDescription->setOption('edit', 'list');
        $fieldDescription->setAssociationMapping(array('fieldName' => 'gallery', 'type' => \Doctrine\ORM\Mapping\ClassMetadataInfo::MANY_TO_ONE));

        $builder = $formMapper->create('galleryId', 'sonata_type_model_list', array(
            'sonata_field_description' => $fieldDescription,
            'class'                    => $this->getGalleryAdmin()->getClass(),
            'model_manager'            => $this->getGalleryAdmin()->getModelManager(),
        ));

        return $builder;
    }

    protected function getGalleryAdmin()
    {
        return $this->container->get('sonata.media.admin.gallery');
    }

    public function prePersist(BlockInterface $block)
    {
        $block->setSetting('galleryId', is_object($block->getSetting('galleryId')) ? $block->getSetting('galleryId')->getId() : null);
    }

    public function preUpdate(BlockInterface $block)
    {
        $block->setSetting('galleryId', is_object($block->getSetting('galleryId')) ? $block->getSetting('galleryId')->getId() : null);
    }

    public function load(BlockInterface $block)
    {
        $gallery = $block->getSetting('galleryId');

        if ($gallery) {
            $gallery = $this
                ->getGalleryAdmin()
                ->getModelManager()
                ->find($this->getGalleryAdmin()->getClass(), $gallery);
        }

        $block->setSetting('galleryId', $gallery);
    }

    /**
     * {@inheritdoc}
     */
    public function getJavascripts($media)
    {
        return array(
            'https://cdnjs.cloudflare.com/ajax/libs/fancybox/2.1.5/jquery.fancybox.pack.js',
            'https://cdnjs.cloudflare.com/ajax/libs/fancybox/2.1.5/helpers/jquery.fancybox-media.js',
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getStylesheets($media)
    {
        return array(
            'https://cdnjs.cloudflare.com/ajax/libs/fancybox/2.1.5/jquery.fancybox.min.css',
        );
    }


}
