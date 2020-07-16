<?php

namespace Core\UserBundle\Controller;

use Core\UserBundle\Form\Type\ProfileFormType;
use Core\UserBundle\Mailer\Mailer;
use Core\UtilityBundle\Rest\ApiHelper;
use DateTime;
use DoctrineEncryptedFieldTypeBundle\Service\PseudonymizerService;
use Exception;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\View\View;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseNullableUserEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\Form\Factory\FactoryInterface;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use FOS\UserBundle\Util\TokenGeneratorInterface;
use Nelmio\ApiDocBundle\Annotation\Security as SWGSecurity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Swagger\Annotations as SWG;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class ProfileApiController extends AbstractFOSRestController
{
    /** @var UserManagerInterface */
    private $userManager;

    /** @var ApiHelper */
    private $apiHelper;

    /** @var Mailer */
    private $mailer;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /** @var TokenGeneratorInterface */
    private $tokenGenerator;

    /** @var FactoryInterface */
    private $fosFormFactory;

    /** @var PseudonymizerService */
    private $pseudonymizerService;

    /** @var ParameterBagInterface */
    private $parameterBag;

    public function __construct(
        UserManagerInterface $userManager,
        ApiHelper $apiHelper,
        Mailer $mailer,
        EventDispatcherInterface $eventDispatcher,
        TokenGeneratorInterface $tokenGenerator,
        FactoryInterface $resettingFormFactory,
        PseudonymizerService $pseudonymizerService,
        ParameterBagInterface $parameterBag
    ) {
        $this->userManager          = $userManager;
        $this->apiHelper            = $apiHelper;
        $this->mailer               = $mailer;
        $this->parameterBag         = $parameterBag;
        $this->eventDispatcher      = $eventDispatcher;
        $this->fosFormFactory       = $resettingFormFactory;
        $this->pseudonymizerService = $pseudonymizerService;
        $this->tokenGenerator       = $tokenGenerator;
    }

    /**
     * Returns the profile data for the currently signed in user.
     *
     * @SWG\Response(
     *     response=200,
     *     description="Returns the profile data of the user",
     *     @SWG\Schema(
     *         type="object",
     *         example={
     *           "data": {
     *             "email": "person@gmail.com",
     *             "firstname": "Person",
     *             "lastname": "McPerson",
     *             "gender": "m",
     *             "date_of_birth": "1998-01-02",
     *             "biography": "Hello, I am Person.",
     *             "id": 1,
     *           },
     *           "status": "success",
     *         })
     *     )
     * )
     * @SWGSecurity(name="Bearer")
     *
     * @Security("is_granted('ROLE_USER')")
     * @param Request $request
     * @return View
     */
    public function getProfileAction(Request $request)
    {
        $user     = $this->getUser();
        $userData = $this->getFormUserData($user);

        return $this->view([
            'data'   => $userData,
            'status' => 'success',
        ]);
    }

    /**
     * Updates the profile data of the currently signed in user.
     *
     * Only fields present in the request parameters will be updated. The rest will be ignored.
     *
     * @SWG\Response(
     *     response=200,
     *     description="Profile data updated",
     *     @SWG\Schema(
     *         type="object",
     *         example={
     *           "status": "success",
     *           "data": {
     *             "email": "person@gmail.com",
     *             "firstname": "Updated Person",
     *             "lastname": "Updated McPerson",
     *             "gender": "f",
     *             "date_of_birth": "1998-02-01",
     *             "biography": "Hello, I am Updated Person.",
     *             "id": 1,
     *           },
     *         }
     *     )
     * )
     * @SWG\Response(
     *     response=400,
     *     description="Validation error occurred",
     *     @SWG\Schema(
     *         type="object",
     *         example={
     *           "status": "validation_error",
     *           "message": "validation_error",
     *           "errors": {
     *             "date_of_birth": "Ez az érték nem érvényes.",
     *             "global": {},
     *           },
     *         }
     *     )
     * )
     * @SWG\Parameter(
     *     name="order",
     *     in="query",
     *     type="string",
     *     description="The field used to order rewards"
     * )
     * @SWGSecurity(name="Bearer")
     *
     * @Security("is_granted('ROLE_USER')")
     * @param Request $request
     * @return View
     */
    public function patchProfileAction(Request $request)
    {
        $user = $this->getUser();

        /** @var Form $form */
        $form = $this->get('form.factory')->createNamed('data', ProfileFormType::class, $user, [
            'csrf_protection' => false,
            'method'          => 'PATCH',
        ]);

        $form->handleRequest($request);

        if (!$form->isSubmitted()) {
            return $this->view(['status' => 'not_submitted'], Response::HTTP_BAD_REQUEST);
        }

        if (!$form->isValid()) {
            $errors = $this->apiHelper->serializeFormErrors($form);
            return $this->view(
                [
                    'status'  => 'validation_error',
                    'message' => 'validation error',
                    'errors'  => $errors,
                ],
                Response::HTTP_BAD_REQUEST
            );
        }

        $this->userManager->updateUser($user);

        return $this->view([
            'status' => 'success',
            'data'   => $this->getFormUserData($user),
        ]);
    }

    /**
     * @param Request $request
     * @return View
     */
    public function deleteProfileAction(Request $request)
    {
        /** @var UserInterface $user */
        $user = $this->getUser();

        $user->setEnabled(false);
        $this->userManager->updateUser($user);
        $this->pseudonymizerService->pseudonymizeFields($user);

        $this->userManager->deleteUser($user);

        return $this->view([
            'status' => 'success',
        ]);
    }

    public function postChange_passwordAction(Request $request)
    {
        $user = $this->getUser();

        if (!is_object($user) || !$user instanceof UserInterface) {
            throw new AccessDeniedException('This user does not have access to this section.');
        }

        $event = new GetResponseUserEvent($user, $request);
        $this->eventDispatcher->dispatch($event, FOSUserEvents::CHANGE_PASSWORD_INITIALIZE);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }

        $form = $this->fosFormFactory->createForm();
        $form->setData($user);

        $form->handleRequest($request);

        if (!$form->isSubmitted()) {
            return $this->view(['status' => 'not_submitted'], Response::HTTP_BAD_REQUEST);
        }

        if (!$form->isValid()) {
            $errors = $this->apiHelper->serializeFormErrors($form);
            return $this->view(
                [
                    'status'  => 'validation_error',
                    'message' => 'validation error',
                    'errors'  => $errors,
                ],
                Response::HTTP_BAD_REQUEST
            );
        }

        $event = new FormEvent($form, $request);
        $this->eventDispatcher->dispatch($event, FOSUserEvents::CHANGE_PASSWORD_SUCCESS);

        $this->userManager->updateUser($user);

        if (null === $response = $event->getResponse()) {
            return $this->view([
                'status'  => 'success',
                'message' => 'password.changed',
            ]);
        }

        $this->eventDispatcher->dispatch(
            new FilterUserResponseEvent($user, $request, $response),
            FOSUserEvents::CHANGE_PASSWORD_COMPLETED
        );

        return $response;
    }

    /**
     * @param Request $request
     * @return View|Response
     * @throws Exception
     */
    public function postRecover_passwordAction(Request $request)
    {
        // admin@flex-starter.local
        $usernameOrEmail = $request->request->get('email');

        $user = $this->userManager->findUserByUsernameOrEmail($usernameOrEmail);

        $event = new GetResponseNullableUserEvent($user, $request);
        $this->eventDispatcher->dispatch($event, FOSUserEvents::RESETTING_SEND_EMAIL_INITIALIZE);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }

        $retryTtl = $this->parameterBag->get('fos_user.resetting.retry_ttl');
        if (null !== $user && !$user->isPasswordRequestNonExpired($retryTtl)) {
            $event = new GetResponseUserEvent($user, $request);
            $this->eventDispatcher->dispatch($event, FOSUserEvents::RESETTING_RESET_REQUEST);

            if (null !== $event->getResponse()) {
                return $event->getResponse();
            }

            if (null === $user->getConfirmationToken()) {
                $user->setConfirmationToken($this->tokenGenerator->generateToken());
            }

            $event = new GetResponseUserEvent($user, $request);
            $this->eventDispatcher->dispatch($event, FOSUserEvents::RESETTING_SEND_EMAIL_CONFIRM);

            if (null !== $event->getResponse()) {
                return $event->getResponse();
            }

            $this->mailer->sendResettingEmailMessage($user);
            $user->setPasswordRequestedAt(new DateTime());
            $this->userManager->updateUser($user);

            $event = new GetResponseUserEvent($user, $request);
            $this->eventDispatcher->dispatch(FOSUserEvents::RESETTING_SEND_EMAIL_COMPLETED, $event);

            if (null !== $event->getResponse()) {
                return $event->getResponse();
            }
        } else {
            $error = $user == null ? 'no_user_found' : 'password_request_currently_active';
            return $this->view([
                'success' => false,
                'error'   => $error,
            ]);
        }

        return $this->view([
            'success' => true,
            'message' => 'password_reset_email_sent',
        ]);
    }

    /**
     * @param Request $request
     * @param $token
     * @return View|RedirectResponse|Response|null
     */
    public function postReset_passwordAction(Request $request, $token)
    {
        $user = $this->userManager->findUserByConfirmationToken($token);

        if (null === $user) {
            return $this->view([
                'success' => false,
                'errors'  => 'user_not_found_by_token',
            ]);
        }

        $event = new GetResponseUserEvent($user, $request);
        $this->eventDispatcher->dispatch($event, FOSUserEvents::RESETTING_RESET_INITIALIZE);

        if (null !== $event->getResponse()) {
            return $event->getResponse();
        }

        $form = $this->fosFormFactory->createForm();
        $form->setData($user);

        $form->handleRequest($request);
        // $form->submit($request->request->all());

        if ($form->isSubmitted() && $form->isValid()) {
            $event = new FormEvent($form, $request);
            $this->eventDispatcher->dispatch($event, FOSUserEvents::RESETTING_RESET_SUCCESS);

            $this->userManager->updateUser($user);

            if (null === $response = $event->getResponse()) {
                return $this->view([
                    'success' => true,
                    'message' => 'password_reset_successfully',
                ]);
            }

            $this->eventDispatcher->dispatch(
                new FilterUserResponseEvent($user, $request, $response),
                FOSUserEvents::RESETTING_RESET_COMPLETED
            );

            return $response;
        }

        $errors = !$form->isSubmitted() ? 'bad_request' : $this->apiHelper->serializeFormErrors($form);
        return $this->view([
            'success' => false,
            'errors'  => $errors,
        ]);
    }

    /**
     * @param UserInterface $user
     * @return array
     */
    protected function getFormUserData(UserInterface $user): array
    {
        $userData = [
            'email'         => $user->getEmail(),
            'firstname'     => $user->getFirstname(),
            'lastname'      => $user->getLastname(),
            'gender'        => $user->getGender(),
            'date_of_birth' => $user->getDateOfBirth(),
            'biography'     => $user->getBiography(),
            'phone'         => $user->getPhone(),
            'id'            => $user->getId(),
        ];

        return $userData;
    }
}
