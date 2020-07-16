<?php


namespace Core\UtilityBundle\Validator\Constraint;


use Symfony\Component\Validator\Constraint;

class NameFormat extends Constraint
{
    public $message = 'The format of the name is not valid';
}
