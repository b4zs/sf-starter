<?php


namespace Core\MediaBundle\Block;


use Sonata\AdminBundle\Form\FormMapper;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Model\BlockInterface;
use Sonata\MediaBundle\Block\MediaBlockService as CoreMediaBlockService;
use Sonata\MediaBundle\Model\MediaInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * @deprecated
 */
class MediaBlockService extends CoreMediaBlockService
{
	protected function getFormatChoices(MediaInterface $media = null)
	{
		$result = parent::getFormatChoices($media);
		$result['reference'] = 'reference';

		return $result;
	}

	public function setDefaultSettings(OptionsResolverInterface $resolver)
	{
		parent::setDefaultSettings($resolver);

		$resolver->setDefaults(array(
			'class' => '',
		));
	}

	public function buildEditForm(FormMapper $formMapper, BlockInterface $block)
	{
		if (!$block->getSetting('mediaId') instanceof MediaInterface) {
			$this->wakeUpBlockSettings($block);
		}

		if ($block->getSetting('mediaId') instanceof MediaInterface) {
			$formatChoices = $this->getFormatChoices($block->getSetting('mediaId'));
		} else {
			$formatChoices = array();
		}

		$formMapper->add('settings', 'sonata_type_immutable_array', array(
			'keys' => array(
				array('title', 'text', array('required' => false)),
				array($this->getMediaBuilder($formMapper), null, array()),
				array('format', 'choice', array('required' => count($formatChoices) > 0, 'choices' => $formatChoices)),
			),
		));


		$formMapper->get('settings')->add('class', 'text', array('required' => false,));
	}

	public function wakeUpBlockSettings(BlockInterface $block)
	{
		$media = $block->getSetting('mediaId', null);

		if (is_int($media)) {
			$media = $this->mediaManager->findOneBy(array('id' => $media));
		}

		$block->setSetting('mediaId', $media);
	}

	public function load(BlockInterface $block)
	{
	}

    /**
     * {@inheritdoc}
     */
    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {
        $return = parent::execute($blockContext, $response);

        return $return;
    }


    public function renderResponse($view, array $parameters = array(), Response $response = null)
    {
        $media = null;
        $block = $parameters['block'];
        $mediaId = $block->getSetting('mediaId');

        if ($mediaId && is_numeric($mediaId)) {
            $media = $this->mediaManager->findOneBy(array('id' => $mediaId));
        }

        if ($media instanceof MediaInterface) {
            $format = $parameters['settings']['format'] ? $parameters['settings']['format'] : 'reference';
            $provider = $this->container->get('sonata.media.pool')->getProvider($media->getProviderName());
            $host = $this->container->get('request')->getHttpHost();
            $parameters['media_url'] = '//' . $host . $provider->generatePublicUrl($media, $format);
        }

        return parent::renderResponse($view, $parameters, $response);
    }



}
