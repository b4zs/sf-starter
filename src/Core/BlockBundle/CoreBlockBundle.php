<?php

namespace Core\BlockBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class CoreBlockBundle extends Bundle
{
    public function getParent()
    {
        return 'SonataBlockBundle';
    }
}
