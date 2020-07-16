<?php


namespace Core\UtilityBundle\Validator\Constraint;


use Symfony\Component\Validator\Constraint;

class PhoneFormat extends Constraint
{
    public $message = 'The format of the phone number is not valid';

}
