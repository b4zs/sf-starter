<?php


namespace Core\UtilityBundle\Validator\Constraint;


use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class NameFormatValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$value) {
            return;
        }

        $valid = true;

        if (!preg_match('/^([\w\. \-]+)$/u', $value)) {
            $valid = false;
        }
        if (!$valid) {
            $this->context->addViolation($constraint->message);
        }
    }
}
