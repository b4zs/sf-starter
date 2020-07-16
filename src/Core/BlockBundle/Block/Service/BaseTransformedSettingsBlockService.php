<?php


namespace Core\BlockBundle\Block\Service;


use Sonata\BlockBundle\Block\BaseBlockService;
use Sonata\BlockBundle\Model\BlockInterface;

abstract class BaseTransformedSettingsBlockService extends BaseBlockService
{

    public function load(BlockInterface $block)
    {
        $block->setSettings($this->reverseTransformSettings($block->getSettings()));
        parent::load($block);
    }

    public function prePersist(BlockInterface $block)
    {
        $block->setSettings($this->transformSettings($block->getSettings()));
        parent::prePersist($block);
    }

    public function preUpdate(BlockInterface $block)
    {
        $block->setSettings($this->transformSettings($block->getSettings()));
        parent::preUpdate($block);
    }

    protected function transformSettings(array $settings)
    {
        return $settings;
    }

    protected function reverseTransformSettings(array $settings)
    {
        return $settings;
    }
}