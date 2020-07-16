<?php

namespace Application\MediaBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class ApplicationMediaBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'CoreMediaBundle';
    }
}
