<?php


namespace Core\ClassificationBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;

interface CategoryCollectionAwareGroupInterface
{
    public function getCategories();
    public function setCategories(ArrayCollection $categories);
}