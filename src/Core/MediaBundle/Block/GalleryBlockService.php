<?php


namespace Core\MediaBundle\Block;


use Sonata\AdminBundle\Form\FormMapper;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\MediaBundle\Block\GalleryBlockService as BaseGalleryBlockService;
use Sonata\MediaBundle\Model\MediaInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @deprecated
 */
class GalleryBlockService extends BaseGalleryBlockService
{
    const SERVICE_ID = 'sonata.media.block.gallery';

    public function buildEditForm(FormMapper $formMapper, BlockInterface $block)
    {
        parent::buildEditForm($formMapper, $block);

        /** @var FormBuilder $settings */
        $settings = $formMapper->get('settings');
        $settings->add('template', ChoiceType::class, array(
            'required' => true,
            'choices' => $this->getTemplateChoices(),
        ));
    }

    private function getTemplateChoices()
    {
        $site = $this->container->get('sonata.page.site.selector')->retrieve();
        $templateHelper = $this->container->get('core.page.theming.theme_helper');
        $choices = $templateHelper->getBlockTemplateChoicesOnSite(self::SERVICE_ID, $site);

        return $choices;
    }

    public function configureSettings(OptionsResolver $resolver)
    {
        parent::configureSettings($resolver);
        $resolver->setDefault('vars', []);
    }
}
