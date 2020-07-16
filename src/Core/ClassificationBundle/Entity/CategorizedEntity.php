<?php


namespace Core\ClassificationBundle\Entity;

use Sonata\ClassificationBundle\Model\CategoryInterface;

interface CategorizedEntity
{
    /**
     * @return CategoryInterface
     */
    public function getCategory();

    public function setCategory(CategoryInterface $category);
} 