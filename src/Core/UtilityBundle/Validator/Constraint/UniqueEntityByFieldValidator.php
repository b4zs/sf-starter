<?php


namespace Core\UtilityBundle\Validator\Constraint;


//use Core\GdprBundle\Encryptor\Hasher;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UniqueEntityByFieldValidator extends ConstraintValidator
{
    /** @var EntityManagerInterface */
    private $entityManager;

//    /** @var Hasher */
//    private $encryptor;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }


    public function validate($value, Constraint $constraint)
    {
        $entityClass = $constraint->entityClass;
        $field = $constraint->field;
        /** @var EntityRepository $repository */
        $repository = $this->entityManager->getRepository($entityClass);

        if (false) {
//        if ($constraint instanceof UniqueEntityByField && $constraint->useEncryptionForQuery) {
//            $queryBuilder = $repository->createQueryBuilder('q');
//            foreach ($constraint->extraCriteria as $criteriaKey => $criteriaValue) {
//                $queryBuilder
//                    ->andWhere('q.'.$criteriaKey.' = :'.$criteriaKey)
//                    ->setParameter($criteriaKey, $criteriaValue);
//            }
//            $this->encryptor->addWhereConditionToQueryBuilder(
//                $queryBuilder,
//                'q.'.$constraint->field,
//                $value
//            );
//
//            $result = $queryBuilder->getQuery()->execute();
//            $existingEntity = count($result) ? current($result) : null;
        } else {
            $existingEntity = $repository->findOneBy(array_merge([$field => $value], $constraint->extraCriteria));
        }

        if (null !== $existingEntity) {
            $this->context->addViolation($constraint->message);
        }
    }

    public function setEncryptor(Hasher $encryptor)
    {
        $this->encryptor = $encryptor;
    }
}
