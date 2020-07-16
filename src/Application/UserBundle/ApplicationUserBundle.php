<?php

namespace Application\UserBundle;

use Application\UserBundle\DependencyInjection\ApplicationUserExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ApplicationUserBundle extends Bundle
{
    public function getContainerExtension()
    {
        return new ApplicationUserExtension();
    }

    public function getParent()
    {
        return 'SonataUserBundle';
    }

    public function build(ContainerBuilder $container)
    {
        $extension = $container->getExtension('security');
        $extension->addSecurityListenerFactory(new \Application\UserBundle\Security\Factory\CustomFormLoginFactory());
    }
}
