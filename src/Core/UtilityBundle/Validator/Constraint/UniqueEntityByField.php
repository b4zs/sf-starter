<?php


namespace Core\UtilityBundle\Validator\Constraint;


use Symfony\Component\Validator\Constraint;

class UniqueEntityByField extends Constraint
{
    public $field = null;
    public $entityClass = null;
    public $message = 'Value already in use';
    public $extraCriteria = [];
    public $useEncryptionForQuery = true;

    public function validatedBy()
    {
        return UniqueEntityByFieldValidator::class;
    }


}
