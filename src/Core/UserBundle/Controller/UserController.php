<?php

namespace Core\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserController extends AbstractController
{
    public function login()
    {
        return $this->render('@CoreUser/login.html.twig');
    }

    public function register()
    {
        return $this->render('@CoreUser/register.html.twig');
    }

    public function registerSuccess()
    {
        return $this->render('@CoreUser/registerSuccess.html.twig');
    }

    public function registerConfirm()
    {
        return $this->render('@CoreUser/registerConfirm.html.twig');
    }

    public function profile()
    {
        return $this->render('@CoreUser/profile.html.twig');
    }

    public function passwordChange()
    {
        return $this->render('@CoreUser/password-change.html.twig');
    }

    public function recoverPassword()
    {
        return $this->render('@CoreUser/recover-password.html.twig');
    }

    public function passwordReset($token)
    {
        return $this->render('@CoreUser/password-reset.html.twig', ['token' => $token]);
    }
}
