<?php

namespace Core\UserBundle\Controller;

use Application\UserBundle\Entity\User;
use Core\UserBundle\Mailer\Mailer;
use Core\UtilityBundle\Rest\ApiHelper;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\UserBundle\Controller\RegistrationController;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\Form\Factory\FactoryInterface;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @see RegistrationController
 */
class RegistrationApiController extends AbstractFOSRestController
{
    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /** @var FactoryInterface */
    private $formFactory;

    /** @var UserManagerInterface */
    private $userManager;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var ApiHelper */
    private $apiHelper;

    /** @var Mailer */
    private $mailer;

    public function __construct(
        EventDispatcherInterface $eventDispatcher,
        FactoryInterface $formFactory,
        UserManagerInterface $userManager,
        TokenStorageInterface $tokenStorage,
        ApiHelper $apiHelper,
        Mailer $mailer
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->formFactory     = $formFactory;
        $this->userManager     = $userManager;
        $this->tokenStorage    = $tokenStorage;
        $this->apiHelper       = $apiHelper;
        $this->mailer          = $mailer;
    }

    public function postRegisterAction(Request $request)
    {
        $user = $this->userManager->createUser();
        $user->setEnabled(true);

        $event = new GetResponseUserEvent($user, $request);
        $this->eventDispatcher->dispatch(FOSUserEvents::REGISTRATION_INITIALIZE, $event);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }

        $form = $this->get('form.factory')->createNamed('data', \Core\UserBundle\Form\Type\RegistrationFormType::class,
            $user, [
                'csrf_protection' => false,
            ]);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $event = new FormEvent($form, $request);
                $this->eventDispatcher->dispatch(FOSUserEvents::REGISTRATION_SUCCESS, $event);

                $this->userManager->updateUser($user);

                return $this->view(['status' => 'success']);
            }

            $event = new FormEvent($form, $request);
            $this->eventDispatcher->dispatch(FOSUserEvents::REGISTRATION_FAILURE, $event);

            $errors = $this->apiHelper->serializeFormErrors($form);
            return $this->view(['status' => 'validation_error', 'message' => 'validation error', 'errors' => $errors,],
                Response::HTTP_BAD_REQUEST);
        } else {
            return $this->view(['status' => 'not_submitted'], Response::HTTP_BAD_REQUEST);
        }
    }

    public function postRegisterConfirmAction(Request $request)
    {
        $token       = $request->get('token');
        $userManager = $this->userManager;

        $user = $userManager->findUserByConfirmationToken($token);

        if (null === $user) {
            return $this->view([
                'message' => 'invalid token',
                'status'  => 'fail',
            ], 400);
        }

        $user->setConfirmationToken(null);
        $user->setEnabled(true);

        $event = new GetResponseUserEvent($user, $request);
        $this->eventDispatcher->dispatch(FOSUserEvents::REGISTRATION_CONFIRM, $event);

        $userManager->updateUser($user);

        $this->eventDispatcher->dispatch(FOSUserEvents::REGISTRATION_CONFIRMED,
            new FilterUserResponseEvent($user, $request, new Response()));

        //TODO: authenticate user! - include jwt tokens in response.
        return $this->view([
            'message' => 'account confirmed',
            'status'  => 'success',
        ]);
    }

    public function getMailAction()
    {
        /** @var UserInterface $user */
        $user = $this->container->get('doctrine')
            ->getRepository(User::class)
            ->findOneBy(['email' => 'a@a.com']);

        $this->mailer->sendConfirmationEmailMessage($user);

        return $this->view(['adsf']);
    }
}
