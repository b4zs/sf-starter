<?php

namespace Core\MediaBundle\Form\Type;


use Core\MediaBundle\CoreMediaBundle;
use Core\MediaBundle\Form\DataTransformer\FilePathToMediaDataTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MediaMultipleUploadType extends AbstractType
{
    /** @var \Core\MediaBundle\Form\DataTransformer\FilePathToMediaDataTransformer $fileToPathModelTransformer */
    private $fileToPathModelTransformer;

    /** @param \Core\MediaBundle\Form\DataTransformer\FilePathToMediaDataTransformer $fileToPathModelTransformer */
    public function setFileToPathModelTransformer($fileToPathModelTransformer)
    {
        $this->fileToPathModelTransformer = $fileToPathModelTransformer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if($options['context']){
            $this->fileToPathModelTransformer->setContext($options['context']);
        }
        if($options['provider_name']){
            $this->fileToPathModelTransformer->setProviderName($options['provider_name']);
        }

        $builder->addModelTransformer($this->fileToPathModelTransformer);
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
    }

    public function getParent()
    {
        return 'text';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Core\MediaBundle\Entity\Media',
            'context'       => 'default',
            'provider_name' => 'sonata.media.provider.file',
        ));
    }

    public function getName()
    {
        return 'core_media_multiple_upload_type';
    }
}