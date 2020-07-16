<?php

namespace Core\BlockBundle\Block\Service;

use Sonata\AdminBundle\Form\FormMapper;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Block\Service\TextBlockService as BaseTextBlockService;
use Sonata\BlockBundle\Model\BlockInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TextBlockService extends BaseTextBlockService
{
    const SERVICE_ID = 'sonata.block.service.text';

    /** @var  ContainerInterface */
    private $container;

    public function buildEditForm(FormMapper $formMapper, BlockInterface $block)
    {
        $editorField = array('content', 'ckeditor', array(
//            'event_dispatcher' => $formMapper->getFormBuilder()->getEventDispatcher(),
//            'format_field'   => array('contentFormatter', '[contentFormatter]'),
//            'source_field'   => array('content', '[content]'),
//            'source_field_options'      => array(
//                'attr' => array('class' => 'span10', 'rows' => 20)
//            ),
//            'target_field'   => 'settings',
//            'listener'       => false,
            'config_name' => 'default',
            'enable' => true,
        ));

        $htmlField = array('content', 'textarea', array(
            'attr' => array(
                'style' => 'width: 80%',
                'rows' => 10,
            ),
        ));

        $classField = array('class', null, array(
            'required' => false,
        ));

        $contentFormatterField = array('contentFormatter', 'choice', array(
            'choices' => array('richhtml' => 'richhtml', 'text' => 'text'),
        ));

	    $contentFormatterValue = $block->getSetting('contentFormatter', 'richhtml');

        $fields = array(
            $contentFormatterField,
            in_array($contentFormatterValue, array('0', 'richhtml')) ? $editorField : $htmlField,
            $classField,
            array('template', 'choice', array(
            'choices' => $this->getTemplateChoices(),
            )),
        );

        $formMapper->add('settings', 'sonata_type_immutable_array', array(
            'keys' => $fields,
        ));
    }


    private function getTemplateChoices()
    {
        $choices = array('SonataBlockBundle:Block:block_core_text.html.twig' => 'default');
        if ($this->container->has('sonata.page.site.selector')) {
            $site = $this->container->get('sonata.page.site.selector')->retrieve();
            $templateHelper = $this->container->get('core.page.theming.theme_helper');
            $choices = $choices
                + $templateHelper->getBlockTemplateChoicesOnSite(self::SERVICE_ID, $site);
        }

        return $choices;
    }

    public function configureSettings(OptionsResolver $resolver)
    {
        $templateChoices = array_keys($this->getTemplateChoices());

        $resolver->setDefaults(array(
            'content'           => 'Insert your custom content here',
            'template'          => reset($templateChoices),
            'class'             => '',
            'contentFormatter'  => 'richhtml',
        ));
    }

    public function getName()
    {
        return 'HTML';
    }

    public function setContainer($container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {
        $settings = $this->processBlockContextBeforeRender($blockContext)->getSettings();
        return $this->renderResponse($blockContext->getTemplate(), array(
            'block'     => $blockContext->getBlock(),
            'settings'  => $settings,
            'content'   => $settings['content'],
        ), $response);
    }

    private function processBlockContextBeforeRender(BlockContextInterface $blockContext)
    {
        $event = new GenericEvent($blockContext);
        $this->container->get('event_dispatcher')->dispatch(self::SERVICE_ID.'.before_render', $event);

        return $event->getSubject();
    }


}
