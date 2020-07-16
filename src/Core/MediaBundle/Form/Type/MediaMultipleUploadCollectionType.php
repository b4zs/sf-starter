<?php


namespace Core\MediaBundle\Form\Type;


use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\EventListener\ResizeFormListener;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Collection;

class MediaMultipleUploadCollectionType extends AbstractType
{
    public function __construct()
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['max_chunk_size'] = $options['max_chunk_size'];
    }

    public function getParent()
    {
        return 'collection';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'type'                  => 'core_media_multiple_upload_type',
            'allow_add'             => true,
            'allow_remove'          => true,
            'allow_delete'          => true,
            'delete_empty'          => true,
            'by_reference'          => false,
            'error_bubbling'        => false,
            'cascade_validation'    => true,
            'max_chunk_size'        => 0, //if it is set to 0 no chunk upload, anyway the size of each chunk
            'options'               => array(
                'context'       => 'default',
                'provider_name' => 'sonata.media.provider.file',
            ),
        ));
    }

    public function getName()
    {
        return 'core_media_multiple_upload_collection_type';
    }
}