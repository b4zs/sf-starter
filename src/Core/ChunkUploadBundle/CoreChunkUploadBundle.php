<?php

namespace Core\ChunkUploadBundle;

use Core\ChunkUploadBundle\DependencyInjection\CoreChunkUploadExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class CoreChunkUploadBundle extends Bundle
{
    protected function getContainerExtensionClass()
    {
        return CoreChunkUploadExtension::class;
    }

}
