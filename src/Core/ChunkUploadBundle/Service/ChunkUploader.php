<?php


namespace Core\ChunkUploadBundle\Service;


use Application\MediaBundle\Entity\Media;
use Core\ChunkUploadBundle\Model\Chunk;
use Core\ChunkUploadBundle\Model\MediaInterface;
use Sonata\MediaBundle\Extra\ApiMediaFile;
use Sonata\MediaBundle\Provider\FileProvider;
use Sonata\MediaBundle\Provider\Pool;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\FileBag;

class ChunkUploader
{

    const
        STATUS_IN_PROGRESS = 0,
        STATUS_DONE = 1,
        STATUS_ERROR = 2;

    /** @var string */
    private $mediaClass;

    /** @var Pool */
    private $providerPool;

    public function __construct($mediaClass, Pool $providerPool)
    {
        $this->mediaClass = $mediaClass;
        $this->providerPool = $providerPool;
    }

    /**
     * @param FileBag|UploadedFile $fileBag
     * @param string $contentRangeHeader
     * @param string $sessionId
     * @param string $context
     * @param string $provider
     * @return Media|null
     */
    public function getMedia($fileBag, $contentRangeHeader = null, $sessionId, $context = 'default', $provider = 'sonata.media.provider.file')
    {
        $file = $this->getFile($fileBag);
        $chunkData = new Chunk($contentRangeHeader);
        $media = null;

        //if file has size, chunk upload, anyway normal upload
        if ($chunkData->getTotalSize() > 0) {

            $tmpPartFileName = $this->getPartFileName($sessionId, $file);

            $this->uploadTmpPart($tmpPartFileName, $chunkData->getStart() == 0 ? "wb" : "ab", $file->getPathname());

            if ($chunkData->isLast()) {
                $media = $this->createMedia($tmpPartFileName, $file->getClientOriginalName(), $file->getClientMimeType(), $context, $provider);
            }

        } else {
            $media = $this->createMedia($file, $file->getClientOriginalName(), $file->getClientMimeType(), $context, $provider);
        }


        return $media;
    }

    public function buildProviderGuesser($context)
    {
        $pool = $this->providerPool;

        return function(\SplFileInfo $file) use ($pool, $context) {
            $file = new File($file->getRealPath());

            $fileIsImage = false !== strpos($file->getMimeType(), 'image');

            foreach ($pool->getProvidersByContext($context) as $provider) {
                if (!$provider instanceof FileProvider) {
                    continue;
                }
                $providerIsForImages = false !== strpos($provider->getName(), 'image');
                if ($fileIsImage === $providerIsForImages) {
                    return $provider->getName();
                }
            }
        };
    }


    /**
     * @param array $files
     * @return UploadedFile
     */
    protected function getFileFromBag($files)
    {
        while (!($files instanceof UploadedFile) && (is_array($files) || $files instanceof \IteratorAggregate)) {
            $files = current($files);
        }

        return $files;
    }

    /**
     * @param $file
     * @param $context
     * @param $provider
     * @param bool $isTmp
     * @return MediaInterface
     */
    protected function createMedia($file, $name, $clientMime, $context, $provider, $isTmp = true)
    {
        if (is_string($file) && file_exists($file)) {
            $file = new ApiMediaFile(fopen($file, 'r'));
        } elseif (!$file instanceof \SplFileInfo) {
            throw new \InvalidArgumentException('The provided file must either an instance of \SplFileInfo or a path pointing to a valid file');
        }

        /** @var MediaInterface $media */
        $media = new $this->mediaClass();
        $media->setName($name);
        $media->setBinaryContent($file);
        $media->setContext($context);
        $media->setContentType($clientMime);
        $media->setProviderName(is_callable($provider) ? call_user_func($provider, $file) : $provider);
        $media->setIsTmp($isTmp);

        return $media;
    }

    /**
     * @param $fileBag
     * @return UploadedFile
     */
    protected function getFile($fileBag)
    {
        if ($fileBag instanceof FileBag) {
            $file = $this->getFileFromBag($fileBag->all());

            return $file;
        } elseif ($fileBag instanceof UploadedFile) {
            $file = $fileBag;

            return $file;
        } else {
            throw new \InvalidArgumentException('Th provided argument "$fileBag" must be either an instance of \Symfony\Component\HttpFoundation\FileBag or \Symfony\Component\HttpFoundation\File\UploadedFile');
        }
    }

    protected function uploadTmpPart($targetFileName, $targetFileMode = 'wb', $tmpFileName, $tmpFileMode = 'r')
    {
        $out = @fopen($targetFileName, $targetFileMode);
        if ($out) {
            // Read binary input stream and append it to temp file
            $in = @fopen($tmpFileName, $tmpFileMode);

            if ($in) {
                while ($buff = fread($in, 4096)) {
                    fwrite($out, $buff);
                }
            } else {
                //Failed to open input stream.
                throw new \RuntimeException('Failed to open input stream.');
            }
            @fclose($in);
            @fclose($out);
        } else {
            //Failed to open output stream.
            throw new \RuntimeException('Failed to open output stream.');
        }
    }

//    protected function removeTmpPart($filename)
//    {
//        if(file_exists($filename)){
//            unlink($filename);
//        }
//    }

    /**
     * @param $sessionId
     * @param $file
     * @return string
     */
    protected function getPartFileName($sessionId, $file)
    {
        $safeFilename = preg_replace('/[^\w\._]+/', '_', $file->getClientOriginalName());
        $tmpPartFileName = sprintf('%s/chunk_%s_%s', sys_get_temp_dir(), $sessionId, $safeFilename);

        return $tmpPartFileName;
    }
}
