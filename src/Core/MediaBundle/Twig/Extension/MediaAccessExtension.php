<?php

namespace Core\MediaBundle\Twig\Extension;


use Gaufrette\Exception\FileNotFound;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Model\MediaManagerInterface;
use Sonata\MediaBundle\Provider\ImageProvider;
use Sonata\MediaBundle\Provider\Pool;
use Sonata\MediaBundle\Twig\Extension\MediaExtension;

class MediaAccessExtension extends  \Twig_Extension
{

    protected $mediaService;

    protected $mediaManager;

    protected $environment;

	private $thumbnailDefaultFormat;


	/**
     * @param \Sonata\MediaBundle\Provider\Pool               $mediaService
     * @param \Sonata\MediaBundle\Model\MediaManagerInterface $mediaManager
     */
    public function __construct(Pool $mediaService, MediaManagerInterface $mediaManager, $thumbnailDefaultFormat)
    {
        $this->mediaService = $mediaService;
        $this->mediaManager = $mediaManager;
	    $this->thumbnailDefaultFormat = $thumbnailDefaultFormat;
    }

    /**
     * @param \Sonata\MediaBundle\Model\MediaInterface $media
     * @param array $options
     *
     * @return string
     */
    public function getMediaUnsafeUrl($media = null, $options = array())
    {
        $media = $this->getMedia($media);

        if (null === $media) {
            return '';
        }

        /** @var ImageProvider $provider */
        $provider = $this
            ->getMediaService()
            ->getProvider($media->getProviderName());

        $path = $provider->getReferenceImage($media);
        $path = $provider->getCdn()->getPath($path, true);

        return $path;
    }

    /**
     * @param mixed $media
     *
     * @return null|\Sonata\MediaBundle\Model\MediaInterface
     */
    private function getMedia($media)
    {
        if (!$media instanceof MediaInterface && strlen($media) > 0) {
            $media = $this->mediaManager->findOneBy(array(
                'id' => $media
            ));
        }

        if (!$media instanceof MediaInterface) {
            return null;
        }

        if ($media->getProviderStatus() !== MediaInterface::STATUS_OK) {
            return null;
        }

        return $media;
    }

    /**
     * @return \Sonata\MediaBundle\Provider\Pool
     */
    public function getMediaService()
    {
        return $this->mediaService;
    }


    public function getName()
    {
        'core_media_access';
    }

    public function getFunctions()
    {
        return array(
	        new \Twig_SimpleFunction('media_unsafe_url', array($this, 'getMediaUnsafeUrl')),
	        new \Twig_SimpleFunction('get_thumbnail_path', array($this, 'getThumbnailPath')),
	        new \Twig_SimpleFunction('get_thumbnail_format_path', array($this, 'getThumbnailFormatPath'), array('is_safe' => array(true))),
        );
    }

	public function getFilters()
	{
		return array(
			new \Twig_SimpleFilter('thumbnail_path', array($this, 'getThumbnailPath'))
		);
	}


	public function getThumbnailPath($media, $width, $height, $quality = 90)
	{
		$media = $this->getMedia($media);
		if (null === $media) {
			return null;
		}

		/** @var ImageProvider $provider */
		$provider = $this
			->getMediaService()
			->getProvider($media->getProviderName());

		$referenceImage = $provider->getReferenceImage($media);

		$format = sprintf('%dx%d', $width, $height);
		$settings = array('width' => $width, 'height' => $height, 'quality' => $quality);

		$referenceFile = $provider->getReferenceFile($media);

		if ($provider instanceof ImageProvider) {
			$privateThumbnailUrl = $provider->generatePrivateUrl($media, $format);
			$path = $provider->getCdn()->getPath($privateThumbnailUrl, true);

			if (!$provider->getFilesystem()->getAdapter()->exists($privateThumbnailUrl)) {
				$thumbnailFile = $provider->getFilesystem()->get($privateThumbnailUrl, true);

				try {
					$provider->getResizer()->resize(
						$media,
						$referenceFile,
						$thumbnailFile,
						$this->getExtension($media),
						$settings
					);
				} catch (FileNotFound $e) {
					return null;
				}
				unset($thumbnailFile);
			}

			return $path;
		}

		return null;
	}

	public function getThumbnailFormatPath($media, $format)
	{
		$media = $this->getMedia($media);
		return $this->getMediaService()->getProvider($media->getProviderName())->generatePublicUrl($media, $format);
	}

	/**
	 * @param \Sonata\MediaBundle\Model\MediaInterface $media
	 *
	 * @return string the file extension for the $media, or the $defaultExtension if not available
	 */
	protected function getExtension(MediaInterface $media)
	{
		$ext = $media->getExtension();
		if (!is_string($ext) || strlen($ext) < 3) {
			$ext = $this->thumbnailDefaultFormat;
		}

		return $ext;
	}
}