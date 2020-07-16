<?php


namespace Application\MediaBundle\Admin;


use Core\ClassificationBundle\Entity\Tag;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class GalleryAdmin extends \Core\MediaBundle\Admin\ORM\GalleryAdmin
{
    protected function configureFormFields(FormMapper $formMapper)
    {
        parent::configureFormFields($formMapper);

        $formMapper->with('Options', ['label' => false,'translation_domain' => $this->translationDomain]);

        $formMapper
            ->add('tags', EntityType::class, array(
                'attr' => array('style' => 'width: 100%;'),
                'class' => Tag::class,
                'multiple' => true,
                'required' => false,
            ));

        $formMapper->end();
    }
}
