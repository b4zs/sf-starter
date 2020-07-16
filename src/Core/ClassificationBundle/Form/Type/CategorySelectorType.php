<?php


namespace Core\ClassificationBundle\Form\Type;


use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\PersistentCollection;
use FOS\UserBundle\Model\GroupInterface;
use Sonata\UserBundle\Model\UserInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CategorySelectorType extends CategoryType
{
    /** @var Container */
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function getName()
    {
        return 'classification_category_selector';
    }

    public function getParent()
    {
        return 'classification_category';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'choices' => $this->buildChoices(),
            'expanded' => false,
            'class' => $this->getCategoryEntityClass()
        ));
    }

    private function getCategoryEntityClass()
    {
        $categoryClass = $this->container->getParameter('sonata.classification.admin.category.entity');
        return $categoryClass;
    }

    private function buildChoices()
    {
        $categoryClass = $this->getCategoryEntityClass();
        $user = $this->container->get('security.context')->getToken()->getUser();

        if ($user instanceof UserInterface) {
            /** @var EntityRepository $repository */
            $repository = $this->container->get('doctrine')->getRepository($categoryClass);
            /** @var PersistentCollection $groups */
            $groups = $user->getGroups();
            $groupIds = array_map(function (GroupInterface $group) { return $group->getId(); }, $groups->getValues());

            $queryBuilder = $repository
                ->createQueryBuilder('category');

            $entries = $queryBuilder->getQuery()->execute();

        } else {
            $entries = array();
        }

        $result = array();

        foreach ($entries as $entry) {
            $result[$entry->getId()] = $entry;
        }

        return $result;
    }
}