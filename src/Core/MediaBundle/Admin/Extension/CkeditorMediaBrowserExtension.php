<?php

namespace Core\MediaBundle\Admin\Extension;

use Sonata\AdminBundle\Admin\AdminExtension;

class CkeditorMediaBrowserExtension extends AdminExtension
{
    public function configureRoutes(\Sonata\AdminBundle\Admin\AdminInterface $admin, \Sonata\AdminBundle\Route\RouteCollection $collection)
    {
        $collection->add('browser', 'browser');
        $collection->add('upload', 'upload');
    }
}
