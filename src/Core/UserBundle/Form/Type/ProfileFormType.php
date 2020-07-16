<?php

namespace Core\UserBundle\Form\Type;

use Core\UtilityBundle\Validator\Constraint\PhoneFormat;
use Sonata\UserBundle\Model\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;

class ProfileFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstname')
            ->add('lastname')
            ->add('date_of_birth', DateType::class, [
                'widget' => 'single_text',
            ])
            ->add('website', UrlType::class)
            ->add('biography', TextareaType::class)
            ->add('phone', TextType::class, [
                'constraints' => [
                    new PhoneFormat(),
                ],
            ])
            ->add('gender', ChoiceType::class, [
                'choices' => [
                    'Female'  => User::GENDER_FEMALE,
                    'Male'    => User::GENDER_MALE,
                    'Unknown' => User::GENDER_UNKNOWN,
                ],
            ]);
    }
}
