<?php


namespace Core\MediaBundle\Form\Type;


use Doctrine\Common\Persistence\ObjectManager;
use Core\AdminBundle\Form\DataTransformer\IdToModelTransformer;
use Core\MediaBundle\Admin\ORM\MediaAdmin;
use Lrotherfield\Component\Form\DataTransformer\EntityToIdentifierTransformer;
use Sonata\AdminBundle\Form\DataTransformer\ModelToIdTransformer;
use Sonata\AdminBundle\Model\ModelManagerInterface;
use Sonata\MediaBundle\Provider\Pool;
use Sonata\MediaBundle\Form\DataTransformer\ProviderDataTransformer;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MediaSelectorType extends AbstractType
{
    const
        MEDIA_BROWSER_ROUTE = 'admin_application_media_media_browser';

    protected $pool;

    protected $class;

    protected $objectManager;

    protected $mediaAdmin;
    /** @var Container */
    private $container;


    /**
     * @param Pool   $pool
     * @param string $class
     */
    public function __construct(Pool $pool, $class, Container $container)
    {
        $this->pool  = $pool;
        $this->class = $class;
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transformer = new ModelToIdTransformer($this->container->get('sonata.media.admin.media')->getModelManager(), $options['class']);
        $builder->addModelTransformer($transformer);
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['provider'] = $options['provider'];
        $view->vars['context'] = $options['context'];
        $view->vars['tag'] = $options['tag'];
        $view->vars['media_browser_route'] = self::MEDIA_BROWSER_ROUTE;
        $view->vars['preview'] = $options['preview'];
        $view->vars['del_media_label'] = $options['del_media_label'];
        $view->vars['select_media_label'] = $options['select_media_label'];
        $view->vars['allow_delete'] = $options['allow_delete'];
        $view->vars['media'] = $this->container->get('sonata.media.admin.media')->getModelManager()->find($this->class, $view->vars['value']);
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'class'    => $this->class,
            'provider'      => null,
            'context'       => null,
            'empty_on_new'  => true,
            'new_on_update' => true,
            'tag'           => null,
            'preview'       => true,
            'allow_delete' => true,
            'del_media_label' => 'label.image_unlink',
            'select_media_label' => 'link.select_image',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'text';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'core_media_selector_type';
    }

} 