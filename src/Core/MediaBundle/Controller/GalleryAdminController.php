<?php


namespace Core\MediaBundle\Controller;


use Application\MediaBundle\Entity\GalleryHasMedia;
use Core\ChunkUploadBundle\Form\ChunkUploadCollectionType;
use Doctrine\Common\Collections\ArrayCollection;
use Sonata\MediaBundle\Model\Gallery;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;

class GalleryAdminController extends \Sonata\MediaBundle\Controller\GalleryAdminController
{
    public function multiuploadAction(Request $request)
    {
        $gallery = $tender = $this->preCheck($request);
        $filesOriginal = [];
        $galleryHasMediaByMedia = [];
        foreach ($gallery->getGalleryHasMedias() as $newGalleryHasMedia) {
            if (!$newGalleryHasMedia->getMedia()) continue;

            $galleryHasMediaByMedia[$newGalleryHasMedia->getMedia()->getId()] = $newGalleryHasMedia;
            $filesOriginal[] = $newGalleryHasMedia->getMedia();
        }

        $form = $this->createFormBuilder(
            ['files' => array_values($filesOriginal),],
            ['translation_domain' => 'SonataMediaBundle',]
        )
            ->add('files', ChunkUploadCollectionType::class, [
                'context' => $gallery->getContext(),
                'provider_name' => 'sonata.media.provider.image',
                'translation_domain' => 'SonataMediaBundle',
                'label' => 'label.files',
            ])
            ->add('save', SubmitType::class, [
                'translation_domain' => 'SonataMediaBundle',
                'label' => 'link.save',
            ])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            //TODO: save.
            $newFiles = $form->get('files')->getData();
            $galleryHasMediaCollection = new ArrayCollection();
            foreach ($newFiles as $media) {
                if (in_array($media, $filesOriginal)) {
                    $galleryHasMediaCollection->add($galleryHasMediaByMedia[$media->getId()]);
                    continue; //not deletd, not new
                } else {
                    $newGalleryHasMedia = new GalleryHasMedia();
                    $newGalleryHasMedia->setGallery($gallery);
                    $newGalleryHasMedia->setMedia($media);
                    $newGalleryHasMedia->setEnabled(true);
                    $galleryHasMediaCollection->add($newGalleryHasMedia);
                    if (method_exists($media, 'setIsTmp')) {
                        $media->setIsTmp(false);
                    }
                }
            }

            $gallery->setGalleryHasMedias($galleryHasMediaCollection);
            $this->admin->getModelManager()->update($gallery);
            $this->addFlash('success', $this->trans('message.successful_update', [], 'SonataMediaBundle'));
            return $this->redirect($this->admin->generateObjectUrl('edit', $gallery));
        }

        return $this->renderWithExtraParams('CoreMediaBundle:GalleryAdmin:multiupload.html.twig', [
            'object' => $gallery,
            'form' => $form->createView(),
            'action' => 'multiupload',
        ]);
    }


    /**
     * @param Request $request
     * @return Gallery|null
     */
    private function preCheck(Request $request): Gallery
    {
        $id = $request->get($this->admin->getIdParameter());
        $existingObject = $this->admin->getObject($id);

        if (!$existingObject) {
            throw $this->createNotFoundException(sprintf('unable to find the object with id: %s', $id));
        }

        $this->admin->checkAccess('edit', $existingObject);

        $preResponse = $this->preEdit($request, $existingObject);
        if (null !== $preResponse) {
            return $preResponse;
        }

        $this->admin->setSubject($existingObject);

        return $existingObject;
    }
}
