<?php

namespace Application\UserBundle\DependencyInjection;

use JMS\JobQueueBundle\Entity\CronJob;
use JMS\JobQueueBundle\Entity\Job;
use Sonata\EasyExtendsBundle\Mapper\DoctrineCollector;
use Sonata\UserBundle\Entity\BaseUser;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class ApplicationUserExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $this->registerDoctrineMapping();
    }

    private function registerDoctrineMapping()
    {
        //user (base)
        $this->overrideEntityField(BaseUser::class, 'firstname', [
            'type' => 'encrypted_data_string',
            'length' => 255,
            'nullable' => true,
        ],true);
        $this->overrideEntityField(BaseUser::class, 'lastname', [
            'type' => 'encrypted_data_string',
            'length' => 255,
            'nullable' => true,
        ], true);
        $this->overrideEntityField(BaseUser::class, 'email', [
            'type' => 'encrypted_data_string',
            'length' => 255,
            'nullable' => true,
        ], true);
        $this->overrideEntityField(BaseUser::class, 'emailCanonical', [
            'type' => 'encrypted_data_string',
            'length' => 255,
            'nullable' => true,
        ], true);
        $this->overrideEntityField(BaseUser::class, 'phone', [
            'type' => 'encrypted_data_string',
            'length' => 255,
            'nullable' => true,
        ], true);
        $this->overrideEntityField(BaseUser::class, 'dateOfBirth', [
            'type' => 'encrypted_data_datetime',
            'columnName' => 'date_of_birth',
            'length' => 255,
        ], true);

        DoctrineCollector::getInstance()->addIndex(Job::class, 'cmd_search_index', ['queue']); //avoid creating index on "command" field
        $this->overrideEntityField(CronJob::class, 'command', ['type' => 'string', 'length' => 200, 'unique' => false,]);
    }

    private function overrideEntityField(string $class, string $fieldName, array $fieldMapping, $inherited = false): void
    {
        $fieldMapping['fieldName'] = $fieldName;
        if (!isset($fieldMapping['columnName'])) {
            $fieldMapping['columnName'] = $fieldMapping['fieldName'];
        }
        if (!isset($fieldMapping['nullable'])) {
            $fieldMapping['nullable'] = true;
        }

        if ($inherited) {
            DoctrineCollector::getInstance()->addOverride(
                $class,
                'addInheritedFieldMapping',
                ['fieldName' => $fieldMapping,] //this hacks the hack and should have been avoided, but had no better idea
            );
        } else {
            DoctrineCollector::getInstance()->addOverride(
                $class,
                'setAttributeOverride',
                $fieldMapping
            );
        }
    }
}
