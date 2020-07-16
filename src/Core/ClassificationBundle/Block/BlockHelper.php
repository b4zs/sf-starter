<?php


namespace Core\ClassificationBundle\Block;


use Doctrine\ORM\QueryBuilder;
use Core\AdminBundle\Form\DataTransformer\IdArrayToModelTransformer;
use Core\AdminBundle\Form\DataTransformer\IdToModelTransformer;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class BlockHelper
{
    use ContainerAwareTrait;

    public function createTagsField(FormMapper $form)
    {
        $tagsField = $form->create('tags', 'entity', array(
            'class' => $this->container->get('sonata.classification.manager.tag')->getClass(),
            'multiple' => true,
            'expanded' => false,
            'required' => false,
        ));

        $tagsField->addModelTransformer(new IdArrayToModelTransformer(
            $this->container->get('sonata.admin.manager.orm'),
            $this->container->get('sonata.classification.manager.tag')->getClass()
        ));

        return $tagsField;
    }

    public function createCategoryFields(FormMapper $form)
    {
        $categoryField = $form->create('category', 'classification_category_selector', array(
            'required'  => false,
            'label'     => 'label.category',
        ));
        $categoryField->addModelTransformer(new IdToModelTransformer(
                $this->container->get('sonata.admin.manager.orm'),
                $this->container->get('sonata.classification.manager.category')->getClass())
        );

        return $categoryField;
    }

    public function addFiltersToQueryBySettings(QueryBuilder $queryBuilder, array $settings, $rootAlias)
    {
        $hasFilter = false;

        if (!empty($settings['category'])) {
            $queryBuilder
                ->andWhere(sprintf('%s.category = :category', $rootAlias))
                ->setParameter('category', $settings['category']);
            $hasFilter = true;
        }

        if (!empty($settings['tags']) && is_array($settings['tags'])) {
            $uniqueId = uniqid();
            $joinName = 'join_tag_'.$uniqueId;
            $paramName = 'param_tag'.$uniqueId;

            $queryBuilder
                ->innerJoin(sprintf('%s.tags', $rootAlias), $joinName)
                ->andWhere($joinName.'.id  IN (:'.$paramName.')')
                ->setParameter($paramName, $settings['tags']);

            $hasFilter = true;
        }

        return $hasFilter;
    }


}
