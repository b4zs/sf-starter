<?php
namespace Core\MediaBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Sonata\MediaBundle\Provider\Pool;

/**
 * Class MediaCollectionType
 * @package Core\MediaBundle\Form\Type
 */
class MediaCollectionType extends AbstractType
{
    /**
     * media route
     */
    const MEDIA_BROWSER_ROUTE = 'admin_application_media_media_browser';

    /**
     * @var DataTransformerInterface
     */
    protected $transformer;

    /**
     * @var Pool
     */
    protected $pool;

    /**
     * @param Pool $pool
     * @param DataTransformerInterface $transformer
     */
    public function __construct(Pool $pool, DataTransformerInterface $transformer) {
        $this->transformer = $transformer;
        $this->pool = $pool;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer($this->transformer);
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['provider'] = $options['provider'];
        $view->vars['thumb']['width']  = $options['thumb_width'];
        $view->vars['thumb']['height'] = $options['thumb_height'];
        $view->vars['context'] = $options['context'];
        $view->vars['tag'] = $options['tag'];
        $view->vars['media_browser_route'] = self::MEDIA_BROWSER_ROUTE;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $defaultContext = $this->pool->getContext($this->pool->getDefaultContext());
        $resolver->setDefaults(array(
            'provider'      => null,
            'context'       => null,
            'tag'           => null,
            'allow_add'     => true,
            'allow_delete'  => true,
            'thumb_width'   => array_key_exists('default_thumbnail', $defaultContext['formats']) ? $defaultContext['formats']['default_thumbnail']['width'] : 0,
            'thumb_height'  => array_key_exists('default_thumbnail', $defaultContext['formats']) ? $defaultContext['formats']['default_thumbnail']['height'] : 0
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'collection';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'core_media_collection_type';
    }
}