<?php
namespace Core\MediaBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\FileValidator;

class MediaValidator extends FileValidator
{
    public function validate($value, Constraint $constraint)
    {
        if($value instanceof \Core\MediaBundle\Entity\Media){
            $value = $value->getBinaryContent();
        }

        parent::validate($value, $constraint);
    }
}

