<?php

namespace Core\BlockBundle\Block\Service;

use Core\BlockBundle\Block\Service\MenuBlockService;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Validator\ErrorElement;
use Sonata\BlockBundle\Block\BaseBlockService;
use Sonata\BlockBundle\Model\BlockInterface;

class SitemapBlockService extends MenuBlockService
{

    /**
     * @return array
     */
    protected function getFormSettingsKeys()
    {
        $settingsKeys = array(
            array('title', 'text', array('required' => false)),
            array('menu_name', 'choice', array('choices' => $this->menus->getMenuChoices(), 'required' => true, 'attr' => array('class' => 'span6'))),
            array('menu_template', 'choice', array('choices' => $this->getTemplateChoices(), 'required' => false)),
        );

        return $settingsKeys;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Sitemap';
    }

}