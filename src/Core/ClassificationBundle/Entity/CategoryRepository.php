<?php


namespace Core\ClassificationBundle\Entity;


use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Sonata\UserBundle\Model\UserInterface;

class CategoryRepository extends EntityRepository
{
    public function addFilterForCategory(QueryBuilder $queryBuilder, $categoryAlias, UserInterface $user = null)
    {

    }

} 