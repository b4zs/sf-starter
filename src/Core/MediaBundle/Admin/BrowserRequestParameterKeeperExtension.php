<?php

namespace Core\MediaBundle\Admin;


use Sonata\AdminBundle\Admin\AdminExtension;
use Sonata\AdminBundle\Admin\AdminInterface;
use Symfony\Component\DependencyInjection\Container;

class BrowserRequestParameterKeeperExtension extends AdminExtension
{
    /** @var  Container */
    private $container;

    public function getPersistentParameters(AdminInterface $admin)
    {
        $request = $admin->getRequest();

        $result = array();

        foreach (array('return', 'callback', 'tags') as $key) {
            if ($request->get($key, false)) {
                $result[$key] = $request->get($key);
            }
        }

	    return $result;
    }

    /**
     * @param \Symfony\Component\DependencyInjection\Container $container
     */
    public function setContainer($container)
    {
        $this->container = $container;
    }
}