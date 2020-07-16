<?php

namespace Core\UtilityBundle\Rest;

use Application\UserBundle\Entity\User;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormInterface;

class ApiHelper
{
    /** @var Serializer */
    private $serializer;

    public function __construct(
        SerializerInterface $serializer
    ) {
        $this->serializer = $serializer;
    }

    /**
     * @param FormInterface|Form $form
     * @return mixed
     */
    public function serializeFormErrors(FormInterface $form)
    {
        $serializer                 = new FormErrorsSerializer();
        $errors                     = $serializer->serializeFormErrors($form, false, false);
        $errors['fields']['global'] = $errors['global'];
        return $errors['fields'];
    }

    public function buildUserRecordResponseData(User $user): array
    {
        return [
            'id'      => $user->getId(),
            'data'    => $this->serializeUser($user),
            'enabled' => $user->isEnabled(),
        ];
    }

    public function serializeUser(User $user)
    {
        return $this->serializer->toArray($user);
    }
}
