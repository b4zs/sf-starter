<?php


namespace Core\UtilityBundle\Validator\Constraint;


use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class PhoneFormatValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$value) {
            return;
        }

        $valid = true;

        if (!preg_match('/^\+36([0-9]{9})$/u', $value)) {
            $valid = false;
        }
        if (!$valid) {
            $this->context->addViolation($constraint->message);
        }
    }

}
