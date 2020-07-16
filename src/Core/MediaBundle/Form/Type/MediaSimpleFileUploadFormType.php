<?php

namespace Core\MediaBundle\Form\Type;


use Application\MediaBundle\Entity\FileSet;
use Application\MediaBundle\Entity\Media;
use Application\MediaBundle\Entity\GalleryHasMedia;
use Sonata\MediaBundle\Form\DataTransformer\ProviderDataTransformer;
use Sonata\MediaBundle\Provider\Pool;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MediaSimpleFileUploadFormType extends AbstractType
{
    /** @var  Pool */
    private $pool;

    private $class = 'Application\\MediaBundle\\Entity\\Media';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $providerName = $options['provider'];

        $this->pool->getProvider($providerName)->buildMediaType($builder);

        $builder->addModelTransformer(new ProviderDataTransformer($this->pool, $this->class, array(
            'provider'      => $providerName,
            'context'       => $options['context'],
            'empty_on_new'  => true,
            'new_on_update' => true,
        )));

        $builder->addEventListener(FormEvents::POST_SUBMIT, function(FormEvent $event){
            $form = $event->getForm();
            $media = $form->getData();
            $ghm = new GalleryHasMedia();
            $documentSet = new FileSet();
            $ghm->setGallery($documentSet);
            $ghm->setMedia($media);
            $documentSet->addGalleryHasMedias($ghm);
            return $documentSet;
        });
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {

    }

    public function getName()
    {
        return 'core_media_simple_file_upload';
    }

    /**
     * @param mixed $pool
     */
    public function setPool($pool)
    {
        $this->pool = $pool;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'provider' => 'sonata.media.provider.file',
            'context' => 'default',
            'compound' => true,
            'data_class' => $this->class,
            'error_bubbling' => true,
        ));
    }
}