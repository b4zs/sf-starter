<?php


namespace Core\ChunkUploadBundle\Controller;


use Core\ChunkUploadBundle\Form\DataTransformer\AsymmetricIdToMediaTransformer;
use Core\ChunkUploadBundle\Model\MediaInterface;
use Core\ChunkUploadBundle\Service\ChunkUploader;
use Sonata\MediaBundle\Provider\FileProvider;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ChunkUploadController extends Controller
{

    public function uploadAction(Request $request)
    {
        $chunkUploader = $this->getChunkUploadHelper();
        try {
            $media = $chunkUploader->getMedia(
                $request->files,
                $request->headers->get('content-range', null),
                $request->getSession()->getId(),
                $request->get('context'),
                $request->get('provider',
                    null) ? $request->get('provider') : $chunkUploader->buildProviderGuesser($request->get('context'))
            );
        } catch (\Exception $e) {
            return $this->getResponse(ChunkUploader::STATUS_ERROR, null, $e->getMessage());
        }


        if ($media instanceof \Sonata\MediaBundle\Model\MediaInterface) {
            if (!$media instanceof MediaInterface) {
                throw new \Exception('Media entity incorrectly configured, should implement Core\\ChunkUploadBundle\\Model\\MediaInterface');
            }
            try {
                $this->get('core_chunk_upload.manager.media_manager')->persistMedia($media);
                return $this->getResponse(ChunkUploader::STATUS_DONE, $media);
            } catch (\Exception $e) {
                return $this->getResponse(ChunkUploader::STATUS_ERROR, null, $e->getMessage());
            }
        }

        return $this->getResponse(ChunkUploader::STATUS_IN_PROGRESS);
    }

    private function getChunkUploadHelper()
    {
        return $this->get('core_chunk_upload.chunk_uploader');
    }

    private function getResponse($status, MediaInterface $media = null, $errorMsg = null)
    {
        return new JsonResponse(array(
            'status' => $status,
            'media'  => $media ? $this->getMediaDataBuilder()->buildData($media) : null,
            'error' => $errorMsg,
        ));
    }

    public function getMediaDataBuilder()
    {
        return $this->container->get('core_chunk_upload.builder.media_data_builder');
    }
}
