<?php


namespace Core\ClassificationBundle\Entity;


use Sonata\ClassificationBundle\Model\TagInterface;

class TagManager extends \Sonata\ClassificationBundle\Entity\TagManager
{
    public function getByContext($context, ?bool $enabled = true): array
    {
        $queryBuilder = $this->getObjectManager()
            ->getRepository(TagInterface::class)
            ->createQueryBuilder('t')
            ->andWhere('t.context = :context')->setParameter('context', $context);

        if (null !== $enabled) {
            $queryBuilder->andWhere('t.enabled = :enabled')->setParameter('enabled', $enabled);
        }

        return $queryBuilder->getQuery()->getResult();
    }


}
