<?php


namespace Core\UserBundle\EventListener;


use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Mailer\MailerInterface;
use FOS\UserBundle\Util\TokenGeneratorInterface;

class EmailConfirmationListener extends \FOS\UserBundle\EventListener\EmailConfirmationListener
{
    private $mailer;
    private $tokenGenerator;

    /**
     * EmailConfirmationListener constructor.
     *
     * @param MailerInterface         $mailer
     * @param TokenGeneratorInterface $tokenGenerator
     */
    public function __construct(MailerInterface $mailer, TokenGeneratorInterface $tokenGenerator)
    {
        $this->mailer = $mailer;
        $this->tokenGenerator = $tokenGenerator;
    }

    public function onRegistrationSuccess(FormEvent $event)
    {
        /** @var $user \FOS\UserBundle\Model\UserInterface */
        $user = $event->getForm()->getData();

        $user->setEnabled(false);
        if (null === $user->getConfirmationToken()) {
            $user->setConfirmationToken($this->tokenGenerator->generateToken());
        }

        $this->mailer->sendConfirmationEmailMessage($user);
    }
}
