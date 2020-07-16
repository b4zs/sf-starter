<?php

namespace Core\MediaBundle\DependencyInjection;

use Sonata\EasyExtendsBundle\Mapper\DoctrineCollector;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class CoreMediaExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $mediaClass = $container->getParameter('sonata.media.admin.media.entity');

        if ($container->hasParameter('sonata.classification.admin.tag.entity')) {
            $tagClass = $container->getParameter('sonata.classification.admin.tag.entity');
            $categoryClass = $container->getParameter('sonata.classification.admin.category.entity');
            $collectionClass = $container->getParameter('sonata.classification.admin.collection.entity');


            $this->registerDoctrineMapping(array(
                'tag_class' => $tagClass,
                'media_class' => $mediaClass,
            ));

//            $this->registerDoctrineMappingForCategories(array(
//                'category_class' => $categoryClass,
//                'media_class' => $mediaClass,
//            ));
        }

        $this->fixGalleryHasMediaMapping($mediaClass);
    }

    private function registerDoctrineMapping($config)
    {
        $collector = DoctrineCollector::getInstance();

        $collector->addAssociation($config['media_class'], 'mapManyToMany', array(
            'fieldName'     => 'tags',
            'targetEntity'  => $config['tag_class'],
            'cascade'       => array(
                'persist',
                'refresh',
                'merge',
            ),
            'joinTable'   => array(
                array(
                    'name'     => 'media_tag',
                    'joinColumns' => array('tag_id' => array('referencedColumnName' => 'id')),
                    'inverseJoinColumns' => array('media_id' => array('referencedColumnName' => 'id')),
                ),
            ),
            'orphanRemoval' => false,
        ));
    }

    private function registerDoctrineMappingForCategories($config)
    {
        $collector = DoctrineCollector::getInstance();

        $collector->addAssociation($config['media_class'], 'mapManyToOne', array(
            'fieldName'     => 'category',
            'targetEntity'  => $config['category_class'],
            'cascade'       => array(
                'remove',
                'persist',
                'refresh',
                'merge',
            ),
            'mappedBy'      => NULL,
            'inversedBy'    => NULL,
            'joinColumns'   => array(
                array(
                    'name'     => 'category_id',
                    'referencedColumnName' => 'id',
                    'onDelete' => 'SET NULL',
                ),
            ),
            'orphanRemoval' => false,
        ));
    }

    private function fixGalleryHasMediaMapping($mediaClass)
    {
        $collector = DoctrineCollector::getInstance();
        $collectorRef = new \ReflectionClass($collector);
        $associationsRef = $collectorRef->getProperty('associations');
        $associationsRef->setAccessible(true);
        $associations = $associationsRef->getValue($collector);
        foreach ($associations[$mediaClass]['mapOneToMany'] as $ix => $associationDefinition) {
            if ('galleryHasMedias' === $associationDefinition['fieldName']) {
                $associations[$mediaClass]['mapOneToMany'][$ix]['orphanRemoval'] = true;
                break;
            }
        }
        $associationsRef->setValue($collector, $associations);
    }
}
