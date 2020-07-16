<?php


namespace Core\ChunkUploadBundle\Form;


use Core\ChunkUploadBundle\Form\DataTransformer\AsymmetricIdToMediaTransformer;
use Core\ChunkUploadBundle\Model\MediaInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChunkUploadCollectionRowType extends AbstractType
{

    /** @var string */
    private $mediaClass;

    /** @var AsymmetricIdToMediaTransformer */
    private $mediaTransformer;

    public function __construct($mediaClass, AsymmetricIdToMediaTransformer $mediaTransformer)
    {
        $this->mediaClass = $mediaClass;
        $this->mediaTransformer = $mediaTransformer;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer($this->mediaTransformer);
    }

    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        if ($view->vars['data']) {
            $media = $this->mediaTransformer->buildMediaById($view->vars['data']);
            $view->vars['media'] = $media;
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
        ));
    }

    public function getParent()
    {
        return HiddenType::class;
    }
}
