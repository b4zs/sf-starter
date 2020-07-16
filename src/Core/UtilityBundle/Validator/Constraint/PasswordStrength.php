<?php


namespace Core\UtilityBundle\Validator\Constraint;


use Symfony\Component\Validator\Constraint;

class PasswordStrength extends Constraint
{
    public $tooShortMessage = 'Your password must be at least {{length}} characters long.';
    public $message = 'password_too_weak';
    public $minLength = 6;
    public $minStrength;
    public $unicodeEquality = false;

    public function getDefaultOption()
    {
        return 'minStrength';
    }
    public function getRequiredOptions()
    {
        return ['minStrength'];
    }
}
