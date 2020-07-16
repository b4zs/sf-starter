<?php

declare(strict_types=1);

namespace Core\UtilityBundle\DependencyInjection;

use Core\UtilityBundle\JmsJobQueuePatch\Listener\ManyToAnyListener;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class UtilityExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');

        if ($container->hasParameter('jms_job_queue.entity.many_to_any_listener.class')) {
            $container->setParameter('jms_job_queue.entity.many_to_any_listener.class', ManyToAnyListener::class);
        }
    }
}
