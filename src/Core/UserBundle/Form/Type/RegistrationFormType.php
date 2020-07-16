<?php

namespace Core\UserBundle\Form\Type;

use Core\UtilityBundle\Validator\Constraint\PasswordStrength;
use Core\UtilityBundle\Validator\Constraint\UniqueEntityByField;
use FOS\UserBundle\Form\Type\RegistrationFormType as FOSRegistrationFormType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->remove('username');
        $builder->remove('email');
        $builder->add('email', EmailType::class, array(
            'label'              => 'form.email',
            'translation_domain' => 'FOSUserBundle',
            'constraints'        => [
                new NotBlank(),
                new Email([]),
                new UniqueEntityByField([
                    'field'       => 'emailCanonical',
                    'entityClass' => $options['data_class'],
//                    'extraCriteria' => ['isActive' => true,],
                ]),
            ],
        ));

        $builder->remove('plainPassword');
        $builder->add('plainPassword', RepeatedType::class, array(
            'type'            => PasswordType::class,
            'options'         => array(
                'translation_domain' => 'FOSUserBundle',
                'attr'               => array(
                    'autocomplete' => 'new-password',
                ),
            ),
            'constraints'     => [
                new NotBlank(),
                new PasswordStrength(['minLength' => 2, 'minStrength' => 1]),
            ],
            'first_options'   => array('label' => 'form.password'),
            'second_options'  => array('label' => 'form.password_confirmation'),
            'invalid_message' => 'fos_user.password.mismatch',
        ));
    }

    public function getParent()
    {
        return FOSRegistrationFormType::class;
    }


}
