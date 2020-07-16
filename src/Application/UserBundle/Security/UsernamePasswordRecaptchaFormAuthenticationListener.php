<?php


namespace Application\UserBundle\Security;


use ReCaptcha\ReCaptcha;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Http\Firewall\UsernamePasswordFormAuthenticationListener;

class UsernamePasswordRecaptchaFormAuthenticationListener extends UsernamePasswordFormAuthenticationListener
{
    /** @var ReCaptcha */
    private $reCaptcha;

    public function setReCaptcha(ReCaptcha $reCaptcha)
    {
        $this->reCaptcha = $reCaptcha;
    }

    protected function attemptAuthentication(Request $request)
    {
        $reCaptchaResponse = $request->request->get('g-recaptcha-response');
        $remoteIp = $request->getClientIp();
        $hostname = $request->getHost();

        $reCaptchaResponse = $this->reCaptcha
            ->setExpectedHostname($hostname)
            ->verify($reCaptchaResponse, $remoteIp);

        if (!$reCaptchaResponse->isSuccess()) {
            throw new BadCredentialsException('Invalid captcha');
        }

        return parent::attemptAuthentication($request);
    }
}
