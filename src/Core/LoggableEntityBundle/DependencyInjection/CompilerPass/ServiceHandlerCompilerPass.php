<?php
namespace Core\LoggableEntityBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class ServiceHandlerCompilerPass
 * @package Core\LoggableEntityBundle\DependencyInjection\CompilerPass
 */
class ServiceHandlerCompilerPass implements CompilerPassInterface
{

    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('core.loggable_entity.service')) {
            return;
        }

        $definition = $container->findDefinition(
            'core.loggable_entity.service'
        );

        $taggedServices = $container->findTaggedServiceIds(
            'core.loggable_entity.log_class.handler'
        );
        foreach ($taggedServices as $id => $tags) {
            $definition->addMethodCall(
                'addHandler',
                array(new Reference($id))
            );
        }
    }
}