<?php


namespace Core\ClassificationBundle\Form\Type;

use Sonata\ClassificationBundle\Entity\CategoryManager;
use Sonata\ClassificationBundle\Model\CategoryInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\ChoiceList\SimpleChoiceList;

class SonataCategorySelectorType extends AbstractType
{
    protected $manager;

    /**
     * @param CategoryManagerInterface $manager
     */
    public function __construct(CategoryManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $that = $this;

        $resolver->setDefaults(array(
            'category'          => null,
            'choice_list'       => function (Options $opts, $previousValue) use ($that) {
                    return new SimpleChoiceList($that->getChoices($opts));
                }
        ));
    }

    /**
     * @param Options $options
     *
     * @return array
     */
    public function getChoices(Options $options)
    {
        if (!$options['category'] instanceof CategoryInterface) {
            return array();
        }

        $root = $this->manager->getRootCategory();

        $choices = array();

        $this->childWalker($root, $options, $choices);

        return $choices;
    }

    /**
     * @param CategoryInterface $category
     * @param Options           $options
     * @param array             $choices
     * @param int               $level
     */
    private function childWalker(CategoryInterface $category = null, Options $options, array &$choices, $level = 0)
    {
	    if (null === $category) return;
	    
        $choices[$category->getId()] = sprintf("%s %s", str_repeat('-' , 1 * $level), $category);

        if($category->getChildren() === null ) {
            return;
        }

        foreach ($category->getChildren() as $child) {
            if ($options['category'] && $options['category']->getId() == $child->getId()) {
                continue;
            }
            $this->childWalker($child, $options, $choices, $level + 1);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getParent()
    {
        return 'sonata_type_model';
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'sonata_category_selector';
    }

} 