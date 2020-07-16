<?php

namespace Application\UserBundle\DataFixtures;

use Application\UserBundle\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;

class UserAdminFixtureLoader extends Fixture implements FixtureInterface, FixtureGroupInterface
{
    public function load(ObjectManager $manager)
    {
        if (null === $manager->getRepository(User::class)->findOneBy(['username' => 'admin'])) {
            $user = new User();
            $user->setUsername('admin');
            $user->setEmail('admin@example.com');
            $user->setPlainPassword('admin');
            $user->setRealRoles(['ROLE_SUPER_ADMIN']);
            $user->setEnabled(true);
            $manager->persist($user);
            $manager->flush();
        }
    }

    public static function getGroups(): array
    {
        return ['userAdmin'];
    }
}
