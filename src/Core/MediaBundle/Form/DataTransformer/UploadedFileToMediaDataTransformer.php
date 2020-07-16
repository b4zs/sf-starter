<?php

namespace Core\MediaBundle\Form\DataTransformer;

use Application\MediaBundle\Entity\Media;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploadedFileToMediaDataTransformer implements DataTransformerInterface
{

    /**
     * @var string
     */
    private $context;

    /**
     * @var string
     */
    private $providerName;

    /**
     * @var \Sonata\MediaBundle\Provider\Pool
     */
    private $mediaService;

    private $kernelRootDir;

    /**
     * UploadedFileToMediaDataTransformer constructor.
     * @param \Sonata\MediaBundle\Provider\Pool $mediaService
     */
    public function __construct(\Sonata\MediaBundle\Provider\Pool $mediaService, $kernelRootDir)
    {
        $this->mediaService = $mediaService;
        $this->kernelRootDir = $kernelRootDir;
    }


    /**
     * @param string $context
     */
    public function setContext($context)
    {
        $this->context = $context;
    }

    /**
     * @param string $providerName
     */
    public function setProviderName($providerName)
    {
        $this->providerName = $providerName;
    }

    public function transform($value)
    {
        if(is_null($value)){
            return $value;
        }

//        if($value instanceof Media){
//            $provider = $provider = $this->mediaService->getProvider($value->getProviderName());
//
//            $file = new UploadedFile(
//                str_replace('app', 'web', $this->kernelRootDir).$provider->generatePublicUrl($value, 'reference'),
//                $value->getName(),
//                $value->getContentType(),
//                $value->getSize()
//            );
//
//            $value = $file;
//        }else{
//            $value = null;
//            throw new TransformationFailedException('The value must be an instance of "Application\MediaBundle\Entity\Media"');
//        }

        if(!$value instanceof Media){
            $value = null;
            throw new TransformationFailedException('The value must be an instance of "Application\MediaBundle\Entity\Media"');
        }

        return $value;

    }

    public function reverseTransform($value)
    {

        if(is_null($value)){
            return $value;
        }

        if($value instanceof UploadedFile) {
            $media = new Media();
            $media->setBinaryContent($value);
            $media->setContext($this->context ?: 'default');
            $media->setProviderName($this->providerName ?: 'sonata.media.provider.file');
            $media->setName($value->getClientOriginalName());

            $value = $media;
        }elseif(!$value instanceof Media){
            $value = null;
            throw new TransformationFailedException('The value must be an instance of "Symfony\Component\HttpFoundation\File\UploadedFile" or "Application\MediaBundle\Entity\Media"');
        }

        return $value;

    }


}