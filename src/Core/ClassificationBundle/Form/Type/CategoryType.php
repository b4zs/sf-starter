<?php

namespace Core\ClassificationBundle\Form\Type;

use Core\ClassificationBundle\Entity\Category;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Exception\LogicException;
use Symfony\Component\Form\Extension\Core\DataTransformer\ChoicesToBooleanArrayTransformer;
use Symfony\Component\Form\Extension\Core\DataTransformer\ChoicesToValuesTransformer;
use Symfony\Component\Form\Extension\Core\DataTransformer\ChoiceToBooleanArrayTransformer;
use Symfony\Component\Form\Extension\Core\DataTransformer\ChoiceToValueTransformer;
use Symfony\Component\Form\Extension\Core\EventListener\FixCheckboxInputListener;
use Symfony\Component\Form\Extension\Core\EventListener\FixRadioInputListener;
use Symfony\Component\Form\Extension\Core\EventListener\MergeCollectionListener;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\View\ChoiceView;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CategoryType extends AbstractType
{
    /** @var Container */
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function getName()
    {
        return 'classification_category';
    }

    public function getParent()
    {
        return 'entity';
    }


    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'choices' => $this->buildChoices(),
            'expanded' => false,
            'class' => $this->getCategoryEntityClass()
        ));
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['choices_serialized'] = $this->serializeChoicesForJS($options['choices']);
    }

    private function buildChoices()
    {
        $categoryClass = $this->getCategoryEntityClass();
        $repository = $this->container->get('doctrine')->getRepository($categoryClass);
        $entries =  $repository->findAll();
        $result = array();

        foreach ($entries as $entry) {
            $result[$entry->getId()] = $entry;
        }

        return $result;
    }

    /**
     * @param Category[] $choices
     * @return array
     */
    private function serializeChoicesForJS(array $choices)
    {
        $result = array();
        foreach ($choices as $category) {
            $result[] = array(
                'id' => $category->getId(),
                'text' => $category->getName(),
                'parent' => $category->getParent() instanceof Category && in_array($category->getParent(), $choices)
                        ? $category->getParent()->getId()
                        : null,
            );
        }

        return $result;
    }

    private function getCategoryEntityClass()
    {
        $categoryClass = $this->container->getParameter('sonata.classification.admin.category.entity');
        return $categoryClass;
    }
}