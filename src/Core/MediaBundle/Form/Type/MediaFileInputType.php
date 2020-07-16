<?php

namespace Core\MediaBundle\Form\Type;


use Core\MediaBundle\Form\DataTransformer\UploadedFileToMediaDataTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MediaFileInputType extends AbstractType
{

    /** @var \Core\MediaBundle\Form\DataTransformer\UploadedFileToMediaDataTransformer */
    private $uploadedFileToMediaDataTransformer;

    /**
     * MediaFileInputType constructor.
     * @param UploadedFileToMediaDataTransformer $uploadedFileToMediaDataTransformer
     */
    public function __construct(UploadedFileToMediaDataTransformer $uploadedFileToMediaDataTransformer)
    {
        $this->uploadedFileToMediaDataTransformer = $uploadedFileToMediaDataTransformer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $dataTransformer = $this->uploadedFileToMediaDataTransformer;
        $dataTransformer->setProviderName($options['provider']);
        $dataTransformer->setContext($options['context']);

        $builder->addModelTransformer($dataTransformer);
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);

        $view->vars['provider'] = $options['provider'];
        $view->vars['context'] = $options['context'];

    }


    /**
     * Configures the options for this type.
     *
     * @param OptionsResolver $resolver The resolver for the options.
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'context' => 'default',
            'provider' => 'sonata.media.provider.file',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'file';
    }


    public function getName()
    {
        return 'core_media_file_input';
    }


}