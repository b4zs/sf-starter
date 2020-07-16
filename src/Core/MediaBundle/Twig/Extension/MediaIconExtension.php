<?php


namespace Core\MediaBundle\Twig\Extension;


use Sonata\MediaBundle\Model\MediaInterface;
use Symfony\Bundle\AsseticBundle\Templating\AsseticHelper;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\Templating\Helper\CoreAssetsHelper;

class MediaIconExtension extends \Twig_Extension
{

    const PROVIDER_IMAGE_NAME = 'sonata.media.provider.image';

    /** @var  Container */
    private $container;

    /** @var string */
    private $webDirectory;

    private $logicalBasePath;

    private $extensionMap = array(
        'docx' => 'doc',
        'xlsx' => 'xls',
    );

    public function getName()
    {
        return 'media_icon';
    }

    public function getFunctions()
    {
        return array(
            'media_icon' => new \Twig_SimpleFunction('media_icon', array($this, 'getIconForMedia')),
            'media_is_image' => new \Twig_SimpleFunction('media_is_image', array($this, 'getMediaIsImage')),
        );
    }

    public function getIconForMedia(MediaInterface $media)
    {
        $extension = $this->getExtensionFromMedia($media);
        return $this->getIconFromContentType($extension);
    }

    private function getIconFromContentType($type)
    {
        $type = $this->mapExtension($type);

        $location = $this->buildFileLocationByExtension($type);
        if (!$this->fileExists($location)) {
            $type = 'file';
        }

        return $this->buildFileSrcByExtension($type);
    }

    private function buildFileLocationByExtension($extension)
    {
        return sprintf('%s/%s/%s.%s', $this->webDirectory, $this->logicalBasePath, $extension, $this->getIconsExtension());
    }

    private function buildFileSrcByExtension($extension)
    {
        $logicalPath = sprintf('%s%s.%s', $this->logicalBasePath, $extension, $this->getIconsExtension());

        return '/'.$logicalPath;
    }

    public function setWebDirectory()
    {
        $this->webDirectory = implode('/', func_get_args());
        $this->webDirectory = realpath($this->webDirectory);
    }

    public function setLogicalBasePath($logicalBasePath)
    {
        $this->logicalBasePath = $logicalBasePath;
    }

    public function setContainer($container)
    {
        $this->container = $container;
    }

    private function mapExtension($extension)
    {
        return isset($this->extensionMap[$extension]) ? $this->extensionMap[$extension] : $extension;
    }

    private function getIconsExtension()
    {
        return 'png';
    }

    private function fileExists($location)
    {
        return file_exists($location); // TODO: add caching mechanism to reduce file system access
    }

    private function getExtensionFromMedia(MediaInterface $media)
    {
        $metaData = $media->getProviderMetadata();
        if (is_array($metaData) && !empty($metaData['filename'])) {
            $fileName = $metaData['filename'];
            return pathinfo($fileName, PATHINFO_EXTENSION);
        } else {
            return $this->getExtensionFromMimeType($media->getContentType());
        }
    }

    private function getExtensionFromMimeType($contentType)
    {
        //TODO:
        return 'file';
    }

    public function getMediaIsImage(MediaInterface $media)
    {
        return $media->getProviderName() === static::PROVIDER_IMAGE_NAME && preg_match('/^image/', $media->getContentType());
    }

}