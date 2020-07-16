<?php

namespace DoctrineEncryptedFieldTypeBundle\DependencyInjection;

use Application\UserBundle\Entity\Address;
use Application\UserBundle\Entity\Email;
use Application\UserBundle\Entity\PhoneNumber;
use Doctrine\DBAL\Types\TextType;
use Sonata\EasyExtendsBundle\Mapper\DoctrineCollector;
use Sonata\UserBundle\Entity\BaseUser;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Application\UserBundle\Entity\User;

class CoreDoctrineEncryptedFieldTypeExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config        = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');
    }
}
