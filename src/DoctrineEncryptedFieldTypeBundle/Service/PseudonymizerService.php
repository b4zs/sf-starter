<?php

namespace DoctrineEncryptedFieldTypeBundle\Service;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use DoctrineEncryptedFieldTypeBundle\Encryptor\Hasher;
use InvalidArgumentException;

class PseudonymizerService
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var Hasher */
    private $hasher;

    public function __construct(EntityManagerInterface $entityManager, Hasher $hasher)
    {
        $this->entityManager = $entityManager;
        $this->hasher        = $hasher;
    }

    /**
     * @param string $entityClass Doctrine entity class FQCN
     * @param null|array $fields Optional: name of fields to pseudonymize
     */
    private function getEncryptedFieldsOfEntity(string $entityClass, array $fields = null)
    {
        $metadata = $this->entityManager->getClassMetadata($entityClass);

        if (!$fields || !is_array($fields) || empty($fields)) {
            $fields = array_values($metadata->fieldNames);
        }

        $encryptedFieldTypes = [
            'encrypted_data_string',
            'encrypted_data_datetime',
            'encrypted_data_text',
        ];

        return array_filter($metadata->fieldMappings, function ($field) use ($fields, $encryptedFieldTypes) {
            $isFieldAllowed   = in_array($field['fieldName'], $fields);
            $isFieldEncrypted = in_array($field['type'], $encryptedFieldTypes);

            return $isFieldAllowed && $isFieldEncrypted;
        });
    }

    /**
     * @param null|string|\DateTime $value
     */
    private function pseudonymizeEncryptedValue($value = null)
    {
        $encryptedValue = null;
        if ($value instanceof DateTime) {
            $year = intval($value->format('Y'));

            $encryptedValue = (new DateTime())
                ->setDate($year, 1, 1)
                ->setTime(0, 0, 0, 0);

            $encryptedValue = $this->hasher->encrypt($encryptedValue->format('Y-m-d H:i:s'));
        }

        return Hasher::pseudonymize($value, $encryptedValue);
    }

    /**
     * @param object $entity Doctrine entity object
     * @param null|array $fields Optional: name of fields to pseudonymize
     */
    public function pseudonymizeFields($entity, $fields = null)
    {
        $entityClass     = get_class($entity);
        $encryptedFields = $this->getEncryptedFieldsOfEntity($entityClass, $fields);

        if (empty($encryptedFields)) {
            // TODO: custom exception?
            throw new InvalidArgumentException();
        }

        $qb = $this->entityManager->createQueryBuilder();
        $qb
            ->update($entityClass, 'e')
            ->where('e.id = :id')
            ->setParameter('id', $entity->getId());

        foreach ($encryptedFields as $field) {
            $fieldName               = $field['fieldName'];
            $fieldValue              = call_user_func([$entity, 'get' . ucfirst($fieldName)]);
            $pseudonymizedFieldValue = $this->pseudonymizeEncryptedValue($fieldValue);

            $qb
                ->set('e.' . $fieldName, ':' . $fieldName)
                ->setParameter($fieldName, $pseudonymizedFieldValue);
        }

        $qb->getQuery()->execute();
    }
}
