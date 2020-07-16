<?php


namespace Core\MediaBundle\Block;


use Core\MediaBundle\Entity\GalleryManager;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;

/** @property GalleryManager $galleryManager */
class GalleryListBlockService extends \Sonata\MediaBundle\Block\GalleryListBlockService
{

    public function execute(BlockContextInterface $blockContext, Response $response = null)
    {
        $context = $blockContext->getBlock()->getSetting('context');


        $criteria = [
            'mode' => $blockContext->getSetting('mode'),
            'context' => $context,
            'enabled' => true,
            'tags_or' => $blockContext->getSetting('tags_or'),
        ];

        $order = [
            $blockContext->getSetting('order') => $blockContext->getSetting('sort'),
        ];

        return $this->renderResponse($blockContext->getTemplate(), [
            'context' => $blockContext,
            'settings' => $blockContext->getSettings(),
            'block' => $blockContext->getBlock(),
            'pager' => $this->galleryManager->getPager(
                $criteria,
                1,
                $blockContext->getSetting('number'),
                $order
            ),
        ], $response);
    }

    public function configureSettings(OptionsResolver $resolver)
    {
        $return = parent::configureSettings($resolver);

        $resolver->setDefaults([
            'tags_or' => null,
        ]);

        $return;
    }


}
