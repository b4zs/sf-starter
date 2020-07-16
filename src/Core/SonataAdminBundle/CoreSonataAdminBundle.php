<?php

namespace Core\SonataAdminBundle;

use Core\SonataAdminBundle\DependencyInjection\CoreSonataAdminExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class CoreSonataAdminBundle extends Bundle
{
    public function getContainerExtension()
    {
        return new CoreSonataAdminExtension();
    }
}
