<?php

namespace Core\MediaBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class CoreMediaBundle extends Bundle
{
    public function getParent()
    {
        return 'SonataMediaBundle';
    }
}
