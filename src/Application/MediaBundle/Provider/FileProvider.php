<?php


namespace Application\MediaBundle\Provider;


use Sonata\MediaBundle\Filesystem\Local;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Provider\MediaProviderInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileProvider extends \Sonata\MediaBundle\Provider\FileProvider
{
    /**
     * {@inheritdoc}
     */
    public function getDownloadResponse(MediaInterface $media, $format, $mode, array $headers = [])
    {
        // build the default headers
        $headers = array_merge([
            'Content-Type' => $media->getContentType(),
            'Content-Disposition' => sprintf('attachment; filename="%s"', $media->getName()),
        ], $headers);

        if (!\in_array($mode, ['http', 'X-Sendfile', 'X-Accel-Redirect'], true)) {
            throw new \RuntimeException('Invalid mode provided');
        }

        if ('http' === $mode) {
            if (MediaProviderInterface::FORMAT_REFERENCE === $format) {
                $file = $this->getReferenceFile($media);
            } else {
                $file = $this->getFilesystem()->get($this->generatePrivateUrl($media, $format));
            }

            return new StreamedResponse(static function () use ($file) {
                echo $file->getContent();
            }, 200, $headers);
        }

        if (!$this->getFilesystem()->getAdapter() instanceof Local) {
            throw new \RuntimeException('Cannot use X-Sendfile or X-Accel-Redirect with non \Sonata\MediaBundle\Filesystem\Local');
        }

        $filename = sprintf('%s/%s',
            $this->getFilesystem()->getAdapter()->getDirectory(),
            $this->generatePrivateUrl($media, $format)
        );

        return new BinaryFileResponse($filename, 200, $headers);
    }
}
