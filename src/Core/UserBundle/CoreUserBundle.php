<?php

namespace Core\UserBundle;

use Core\UserBundle\DependencyInjection\UserExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class CoreUserBundle extends Bundle
{
    public function getContainerExtension()
    {
        return new UserExtension();
    }
}
