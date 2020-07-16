<?php


namespace Core\MediaBundle\Form\DataTransformer;


use Application\MediaBundle\Entity\Media;
use Core\MediaBundle\Entity;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\HttpFoundation\File\File;

class FilePathToMediaDataTransformer implements DataTransformerInterface
{

	/**
	 * @var string
	 */
	private $kernelCacheDir;

	/**
	 * @var string
	 */
	private $context;

	/**
	 * @var string
	 */
	private $providerName;

	public function __construct($kernelCacheDir){
		$this->kernelCacheDir = $kernelCacheDir;
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
		if($value instanceof Media){
			$value = $value->getName();
		}else{
			$value = "";
		}

		return $value;
	}


	public function reverseTransform($value)
	{
		$cacheUploadFolder = $this->kernelCacheDir.'/uploads/';
		if(file_exists($cacheUploadFolder.$value)){
			$file = new File($cacheUploadFolder.$value);
			$media = new Media();
			$media->setBinaryContent($file);
			$media->setContext($this->context?:'default');
			$media->setProviderName($this->providerName?:'sonata.media.provider.file');

			if (preg_match('/([\w]{24})_([\d]+)_(.*)/', $value, $matches)) {
				list($tmpFilename, $sessionId, $chunkCount, $originalName) = $matches;
				$media->setName($originalName);
				$media->setMetadataValue('filename', $originalName);
			}

			$value = $media;
		}else{
			$value = null;
			throw new TransformationFailedException(sprintf(
				'The file "%s" does not exists',
				$value
			));
		}

		return $value;
	}
}