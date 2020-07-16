<?php


namespace Core\ChunkUploadBundle\Form;


use Core\ChunkUploadBundle\Form\DataTransformer\CollectionToObjectTransformer;
use Core\ChunkUploadBundle\Model\MediaInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChunkUploadCollectionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if($options['multiple'] !== true){
            $builder->addModelTransformer(new CollectionToObjectTransformer());

            $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event){
                $data = $event->getData();

                if($data instanceof MediaInterface){
                    $event->setData(array($data));
                }

            }, 900);
        }
    }

    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['max_chunk_size'] = $options['max_chunk_size'];
        $view->vars['route_name']     = $options['route_name'];
        $view->vars['context']        = $options['context'];
        $view->vars['provider_name']  = $options['provider_name'];
        $view->vars['multiple']       = $options['multiple'];
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'entry_type'               => \Core\ChunkUploadBundle\Form\ChunkUploadCollectionRowType::class,
            'allow_add'          => true,
            'allow_remove'       => true,
            'allow_delete'       => true,
            'delete_empty'       => true,
            'by_reference'       => false,
            'error_bubbling'     => false,
            'cascade_validation' => true,
            'max_chunk_size'     => 500000, //if it is set to 0 no chunk upload, anyway the size of each chunk
            'route_name'         => 'core_chunk_upload_upload',
            'context'            => 'default',
            'multiple'           => true,
//            'provider_name'      => 'sonata.media.provider.file',
            'provider_name'      => null,
            'prototype_name'     => '__chunk_prototype_name__',
            'options'            => array(
                'label' => false,
            ),
        ));
    }

    public function getParent()
    {
        return CollectionType::class;
    }

    public function getBlockPrefix()
    {
        return 'core_chunk_upload_collection';
    }

}
