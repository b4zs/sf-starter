<?php

namespace Core\UserBundle\Admin\Extension;

use Sonata\AdminBundle\Admin\AbstractAdminExtension;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Event\PreSubmitEvent;
use Symfony\Component\Form\Event\SubmitEvent;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserAdminRequireOwnPasswordForPasswordChange extends AbstractAdminExtension
{
    const FIELD_NAME = 'authorizeActionByPassword';

    public function configureFormFields(FormMapper $formMapper)
    {
        $formMapper->tab('User')->with('General');
        $this->addPasswordField($formMapper, false);
        $formMapper->end()->end();

        $formMapper->getFormBuilder()->addEventListener(FormEvents::SUBMIT, function (SubmitEvent $event) {
            $form = $event->getForm();

            $plainPasswordData = $form->get('plainPassword')->getData();
            $passwordData = $form->get(self::FIELD_NAME)->getData();

            if ($plainPasswordData) {
                $form->remove(self::FIELD_NAME);
                $this->addPasswordField($form, true);
                $form->get(self::FIELD_NAME)->submit($passwordData);
            }
        });
    }

    protected function addPasswordField($formMapper, bool $withConstraints): void
    {
        $options = [
            'label'         => 'Administrator Password for Change Authorization',
            'required'      => false,
            'mapped'        => false,
            'attr'          => ['autocomplete' => 'new-password',],
            'block_prefix'  => 'user_password_for_change_authorization',
        ];

        if ($withConstraints) {
            if ($formMapper instanceof FormMapper) {
                $validationGroups = $formMapper->getFormBuilder()->getOption('validation_groups');
            } elseif ($formMapper instanceof Form) {
                $validationGroups = $formMapper->getConfig()->getOption('validation_groups');
            } else {
                throw new \RuntimeException('Unexpected type of formMapper passed');
            }

            $options['constraints'] = [
                new NotBlank([
                    'groups' => $validationGroups,
                ]),
                new UserPassword([
                    'groups' => $validationGroups,
                ]),
            ];
        }

        $formMapper->add(self::FIELD_NAME, PasswordType::class, $options);
    }
}
