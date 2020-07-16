<?php


namespace Core\ChunkUploadBundle\Builder;


use Doctrine\Common\Util\ClassUtils;
use Core\ChunkUploadBundle\Model\MediaInterface;
use Core\ChunkUploadBundle\Service\MediaIconProvider;
use Sonata\AdminBundle\Admin\Pool as AdminPool;
use Sonata\MediaBundle\Provider\Pool as MediaProviderPool;

class MediaDataBuilder
{

    /** @var MediaIconProvider */
    private $mediaIconProvider;

    /** @var AdminPool */
    private $mediaProviderPool;

    /** @var \Sonata\AdminBundle\Admin\Pool */
    private $adminPool;

    public function __construct(MediaIconProvider $mediaIconProvider, MediaProviderPool $mediaProviderPool, AdminPool $adminPool)
    {
        $this->mediaIconProvider = $mediaIconProvider;
        $this->mediaProviderPool = $mediaProviderPool;
        $this->adminPool = $adminPool;
    }

    public function buildData(MediaInterface $media = null)
    {
        $provider = null === $media ? null : $this->mediaProviderPool->getProvider($media->getProviderName());
        $format = null === $provider ? null : $this->getFormat($provider, $media->getContext(), 'thumbnail');
        $admin = $this->adminPool->getAdminByClass(ClassUtils::getClass($media));

        return [
            'id'            => null === $media ? null : $media->getId(),
            'name'          => null === $media ? null : $media->getName(),
            'extension'     => null === $media ? null : $media->getExtension(),
            'content_type'  => null === $media ? null : str_replace('application/', '', $media->getContentType()),
            'icon'          => null === $media ? null : $this->mediaIconProvider->getClassByExtension($media->getExtension()),
            'thumbnail_url' => null === $media ? null : $provider->generatePublicUrl($media, $format),
            'link'          => null === $media ? null : $provider->generatePublicUrl($media, 'reference'),
            'admin_link'    => null === $admin ? null : $admin->generateObjectUrl('edit', $media),
            'provider'      => null === $provider ? null : $provider->getName(),
        ];
    }

    protected function getFormat($provider, $context, $preferredFormat = 'thumbnail')
    {
        $formats = $provider->getFormats();

        if (isset($formats[$context.'_'.$preferredFormat])) {
            return $context.'_'.$preferredFormat;
        }

        reset($formats);
        $format = key($formats);

        return $format;
    }

}
