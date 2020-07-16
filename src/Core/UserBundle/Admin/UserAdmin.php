<?php

namespace Core\UserBundle\Admin;

use Core\PromoBaseBundle\Entity\PlayerInterface;
use Core\UserBundle\Form\Type\ValidatedPasswordType;
use Core\UtilityBundle\Validator\Constraint\PasswordStrength;
use FOS\UserBundle\Model\UserInterface;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Form\Type\ModelType;
use Sonata\AdminBundle\Mapper\BaseGroupedMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\UserBundle\Form\Type\SecurityRolesType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\LocaleType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class UserAdmin extends \Sonata\UserBundle\Admin\Entity\UserAdmin
{
    protected $baseRouteName = 'admin_user_user_';


    /** @var TokenStorageInterface */
    protected $tokenStorage;

    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper
            ->add('username', null, [
                'template' => 'CoreUserBundle::list__identifier_field.html.twig',
            ])
            ->add('email', null, [
                'template' => 'CoreUserBundle::list__identifier_field.html.twig',
            ])
//            ->add('groups')
            ->add('enabled', null, ['editable' => true])
        ;

        if (in_array(PlayerInterface::class, class_implements( $this->getClass(), true))) {
            $listMapper->add('canWin', null, ['editable' => true,]);
        }

        $listMapper
            ->add('createdAt')
            ->add('deletedAt')
        ;
    }

    protected function configureFields(BaseGroupedMapper $mapper): void
    {
        /** @var UserInterface|null $subject */
        $subject = $this->getSubject();
        $isViewingItself = $subject->getId() === $this->tokenStorage->getToken()->getUser()->getId();
        $isViewingSuperadmin = $subject->hasRole('ROLE_SUPER_ADMIN');
        $now = new \DateTime();

        $genderOptions = [
            'choices' => \call_user_func([$this->getUserManager()->getClass(), 'getGenderList']),
            'required' => true,
            'translation_domain' => $this->getTranslationDomain(),
        ];

        // NEXT_MAJOR: Remove this when dropping support for SF 2.8
        if (method_exists(FormTypeInterface::class, 'setDefaultOptions')) {
            $genderOptions['choices_as_values'] = true;
        }

        $identifierFieldsDisabled = $this->hasSubject() && null !== $this->getSubject()->getId();


        $mapper
            ->tab('User')
                ->with('General', ['class' => 'col-md-6'])
                    ->add('username', null, [
                        'attr' => ['autocomplete' => 'off',],
                        'disabled' => $identifierFieldsDisabled,
                    ])
                    ->add('email', null, [
                        'attr' => ['autocomplete' => 'off',],
                        'disabled' => $identifierFieldsDisabled,
                    ])
                    ->add('phone', null, ['required' => false,])
                    ->add('plainPassword', PasswordType::class, [
                        'label'         => 'Új jelszó beállítása',
                        'required'      => (!$subject || null === $subject->getId()),
                        'attr'          => ['autocomplete' => 'new-password',],
                        'constraints'   => [
                            new PasswordStrength(['minStrength' => 4, 'groups' => ['Registration', 'Profile']]),
                        ],
                    ])
                ->end()
                ->with('Profile', ['class' => 'col-md-6'])
                    ->add('firstname', null, ['required' => false])
                    ->add('lastname', null, ['required' => false])
//                    ->add('dateOfBirth', DatePickerType::class, [
//                        'years' => range(1900, $now->format('Y')),
//                        'dp_min_date' => '1-1-1900',
//                        'dp_max_date' => $now->format('c'),
//                        'required' => false,
//                    ])
                    ->add('gender', ChoiceType::class, $genderOptions)
                    ->add('locale', LocaleType::class, ['required' => false])
//                    ->add('timezone', TimezoneType::class, ['required' => false])
                ->end();
        ;

        if ($mapper instanceof FormMapper) { //use tabs only for EDIT view
            $mapper->end();
            $mapper->tab('Security');
        }

        if (!$isViewingItself && !$isViewingSuperadmin) {
            $mapper
                ->with('Status', ['class' => 'col-md-4'])
                ->add('enabled', $mapper instanceof FormMapper ? CheckboxType::class : 'boolean', ['required' => false])
                ->end();
        }

        if (false && $this->authorizationChecker->isGranted('ROLE_SUPER_ADMIN')) { //TODO: somehow this permission check does not work, to be fixed.
            $mapper
                ->with('Groups', ['class' => 'col-md-4', 'role' => 'ROLE_SUPER_ADMIN'])
                    ->add('groups', ModelType::class, [
                        'required' => false,
                        'expanded' => true,
                        'multiple' => true,
                    ], ['role'     => 'ROLE_SUPER_ADMIN',])
                ->end();
        }


        if ($mapper instanceof FormMapper && !$isViewingItself && !$isViewingSuperadmin) {
            $mapper
                ->with('Roles', ['class' => 'col-md-4', 'role' => 'ROLE_SUPERADMIN',])
                    ->add('realRoles', $mapper instanceof FormMapper ? SecurityRolesType::class : 'sonata_type_immutable_array_legacy', [
                        'label'    => false,
                        'expanded' => true,
                        'multiple' => true,
                        'required' => false,
                    ],['role'     => 'EDIT',])
                ->end();
        }

//        $mapper
//            ->with('Keys')
//                ->add('token', null, ['required' => false])
//                ->add('twoStepVerificationCode', null, ['required' => false])
//            ->end();

        $mapper->end();
    }

    protected function configureFormFields(FormMapper $formMapper): void
    {
        $this->configureFields($formMapper);
    }

    protected function configureShowFields(ShowMapper $showMapper): void
    {
        $this->configureFields($showMapper);
    }

    public function configure()
    {
        unset($this->listModes['mosaic']);
        $this->datagridValues['_sort_by']    = 'id';
        $this->datagridValues['_sort_order'] = 'DESC';
    }

    public function createQuery($context = 'list')
    {
        //TODO: move to app maybe?
        $entityManager = $this->configurationPool->getContainer()->get('doctrine.orm.entity_manager');
        $entityManager->getFilters()->disable('soft_deleteable');
        $return = parent::createQuery($context);

        return $return;
    }

    public function setTokenStorage($tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function setAuthorizationChecker($authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }
}
