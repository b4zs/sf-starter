<?php

namespace Core\ClassificationBundle;

use Core\ClassificationBundle\DependencyInjection\Compiler\ValidationOverrideCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class CoreClassificationBundle extends Bundle
{
    public function getParent()
    {
        return 'SonataClassificationBundle';
    }

    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new ValidationOverrideCompilerPass());
    }


}
