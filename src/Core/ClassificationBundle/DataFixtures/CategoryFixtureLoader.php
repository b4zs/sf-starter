<?php

namespace Core\ClassificationBundle\DataFixtures;

use Core\ClassificationBundle\Entity\Category;
use Core\ClassificationBundle\Entity\Context;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;

class CategoryFixtureLoader extends Fixture implements FixtureInterface, FixtureGroupInterface
{
    public function load(ObjectManager $manager)
    {
        $this->checkAndCreateCategoryAndContext($manager, 'default');
        $this->checkAndCreateCategoryAndContext($manager, 'private');
    }

    private function checkAndCreateCategoryAndContext(ObjectManager $manager, $name): void
    {
        $context = $manager->getRepository(Context::class)->findOneBy(['id' => $name]);
        if (!$context) {
            $context = new Context();
            $context->setName($name);
            $context->setEnabled(true);
            $context->setId($name);
        }

        $category = $manager->getRepository(Category::class)->findOneBy(['slug' => $name]);
        if (!$category) {
            $category = new Category();
            $category->setContext($context);
            $category->setName($name);
            $category->setSlug($name);
        }
        $manager->persist($context);
        $manager->persist($category);

        $manager->flush();
    }

    public static function getGroups(): array
    {
        return ['sonataClassification'];
    }
}
