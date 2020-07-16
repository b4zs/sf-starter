<?php


namespace Application\UserBundle\Security\Factory;


use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\FormLoginFactory;

class CustomFormLoginFactory extends FormLoginFactory
{
    protected function getListenerId()
    {
        return 'Application\UserBundle\Security\UsernamePasswordRecaptchaFormAuthenticationListener';
    }

    public function getKey()
    {
        return 'custom-form-login';
    }



}
