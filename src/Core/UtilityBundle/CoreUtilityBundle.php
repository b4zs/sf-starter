<?php

namespace Core\UtilityBundle;

use Core\UtilityBundle\DependencyInjection\UtilityExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class CoreUtilityBundle extends Bundle
{
    public function getContainerExtension()
    {
        return new UtilityExtension();
    }
}
