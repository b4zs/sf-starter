<?php

namespace Core\LoggableEntityBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Core\LoggableEntityBundle\DependencyInjection\CompilerPass\ServiceHandlerCompilerPass;

/**
 * Class CoreLoggableEntityBundle
 * @package Core\LoggableEntityBundle
 */
class LoggableEntityBundle extends Bundle
{
    /**
     * @param ContainerBuilder $container
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new ServiceHandlerCompilerPass());
    }
}
