<?php

namespace Core\UserBundle\Mailer;

use Application\UserBundle\Entity\User;
use FOS\UserBundle\Model\UserInterface;
use Swift_Attachment;
use Swift_Mailer;
use Swift_Message;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class Mailer extends \FOS\UserBundle\Mailer\Mailer
{
    /** @var Swift_Mailer */
    private $queueMailer;

    /** @var ContainerInterface */
    private $container;

    private function renderTemplate($template, $parameters)
    {
        return $this->templating->render($template, array_merge($parameters, [
            'docroot' => $this->container->getParameter('static_domain'),
        ]));
    }

    public function sendConfirmationEmailMessage(UserInterface $user)
    {
        $template = $this->parameters['confirmation.template'];
        $url      = $this->container->getParameter('frontend_domain')
            . '/register-confirm?token=' . $user->getConfirmationToken();

        $rendered = $this->renderTemplate($template, array(
            'user'            => $user,
            'confirmationUrl' => $url,
        ));

        $this->dispatchEvent($user, 'email.confirmation');
        $this->sendEmailMessage($rendered, $this->parameters['from_email']['confirmation'], (string)$user->getEmail());
    }

    public function sendResettingEmailMessage(UserInterface $user)
    {
        $template = $this->parameters['resetting.template'];
        $url = $this->router->generate('fos_user_resetting_reset', array('token' => $user->getConfirmationToken()), UrlGeneratorInterface::ABSOLUTE_URL);

        $this->dispatchEvent($user, 'email.reset_password');
        $rendered = $this->renderTemplate($template, array(
            'user'            => $user,
            'confirmationUrl' => $url,
        ));
        $this->sendEmailMessage($rendered, $this->parameters['from_email']['resetting'], (string)$user->getEmail());
    }

    public function sendSystemNotificationMessage($content, $subject = 'System notification')
    {
        $template = 'AppBundle:System:system_notification.html.twig';
        $rendered = $this->renderTemplate($template, array(
            'content' => $content,
            'subject' => $subject,
        ));

        $this->sendEmailMessage(
            $rendered,
            $this->parameters['from_email']['resetting'],
            $this->container->getParameter('system_notification_recipient')
        );
    }

    public function sendAdminPasswordAboutToExpireNotificationMessage(User $user)
    {
        $url      = $this->container->get('router')->generate('application_user_admin_profile_change_password', [],
            RouterInterface::ABSOLUTE_URL);
        $template = 'AppBundle:System:admin_password_about_to_expire.html.twig';
        $rendered = $this->renderTemplate($template, array(
            'url' => $url,
        ));

        $this->sendEmailMessage(
            $rendered,
            $this->parameters['from_email']['resetting'],
            $user->getEmail()
        );
    }

    /**
     * @param string $renderedTemplate
     * @param array|string $fromEmail
     * @param array|string $toEmail
     * @param array $attachments
     */
    protected function sendEmailMessage($renderedTemplate, $fromEmail, $toEmail, array $attachments = [])
    {
        $message = $this->prepareSwiftmailMessage($renderedTemplate, $fromEmail, $toEmail, $attachments);
        $this->mailer->send($message);
    }

    /**
     * @param string $renderedTemplate
     * @param array|string $fromEmail
     * @param array|string $toEmail
     * @param array $attachments
     */
    protected function sendQueuedEmailMessage($renderedTemplate, $fromEmail, $toEmail, array $attachments = [])
    {
        $message = $this->prepareSwiftmailMessage($renderedTemplate, $fromEmail, $toEmail, $attachments);
        $this->queueMailer->send($message);
    }

    private function dispatchEvent($user, $emailType)
    {
        $this->container->get('event_dispatcher')->dispatch('user.email',
            new GenericEvent($user, ['email' => $emailType]));
    }

    private function addAttachments($message, array $attachments): void
    {
        foreach ($attachments as $attachment) {
            $entity = new Swift_Attachment($attachment['content'], $attachment['filename'], $attachment['mime']);
            $message->attach($entity);
        }
    }

    public function setQueueMailer(Swift_Mailer $mailer)
    {
        $this->queueMailer = $mailer;
    }

    /**
     * @param $renderedTemplate
     * @param $fromEmail
     * @param $toEmail
     * @param array $attachments
     * @return Swift_Message
     */
    protected function prepareSwiftmailMessage($renderedTemplate, $fromEmail, $toEmail, array $attachments)
    {
        // Render the email, use the first line as the subject, and the rest as the body
        $renderedLines = explode("\n", trim($renderedTemplate));
        $subject       = array_shift($renderedLines);
        $body          = implode("\n", $renderedLines);

        $message = (new Swift_Message())
            ->setSubject($subject)
            ->setFrom($fromEmail)
            ->setTo($toEmail)
            ->setBody($body, 'text/html');

        $this->addAttachments($message, $attachments);
        return $message;
    }

    public function setContainer($container)
    {
        $this->container = $container;
    }
}
