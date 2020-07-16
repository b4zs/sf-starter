<?php

namespace Core\MediaBundle\Serializer;


use Core\MediaBundle\Entity\Media;
use Doctrine\ORM\EntityNotFoundException;
use JMS\Serializer\EventDispatcher\PreSerializeEvent;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Provider\Pool;
use Symfony\Component\HttpFoundation\RequestStack;

class MediaSerializationListener
{
    /** @var Pool */
    private $pool;

    /** @var RequestStack */
    private $requestStack;

    /** @var string */
    private $staticDomain;

    public function __construct(Pool $pool, RequestStack $requestStack, $staticDomain)
    {
        $this->pool = $pool;
        $this->requestStack = $requestStack;
        $this->staticDomain = $staticDomain;
    }

    public function onPreSerialize(PreSerializeEvent $event)
    {
        try {
            if ($event->getObject() instanceof Media && null === $event->getObject()->getDeletedAt()) {
                /** @var Media $media */
                $media = $event->getObject();
                $media->setUrls($this->buildMedia($media));
            }
        } catch (EntityNotFoundException $e) {
        }

        return null;
    }

    private function buildMedia(MediaInterface $media, $format = 'reference')
    {
        $provider = $this->pool->getProvider($media->getProviderName());
        $host = $this->staticDomain;
        $result = array();
        if ('reference' === $format) {
            $formats = array_diff(array_merge(array_keys($provider->getFormats()), array('reference')), array('admin'));
            $context = $media->getContext();
            foreach ($formats as $ix => $format) {
                if (0 !== strpos($format, $context)) {
                    unset($formats[$ix]);
                }
            }
            $formats[] = 'reference';
        } else {
            $formats = array($format);
        }

        foreach ($formats as $format) {
            $result[$format] = '//' . $host . $provider->generatePublicUrl($media, $format);
        }

        return $result;
    }
}