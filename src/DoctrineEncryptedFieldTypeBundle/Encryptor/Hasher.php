<?php

namespace DoctrineEncryptedFieldTypeBundle\Encryptor;

use Cocur\Slugify\Slugify;
use DateTime;
use Doctrine\ORM\QueryBuilder;

class Hasher implements EncryptorInterface
{
    /** @var EncryptorInterface */
    private $encryptor;

    const DELIMITER = '.hash=';

    const HASHING_ALGO = 'md5';

    public function __construct(EncryptorInterface $encryptor)
    {
        $this->encryptor = $encryptor;
    }

    public function encrypt($plainValue)
    {
        return $this->encryptor->encrypt($plainValue) . self::DELIMITER . self::hash($plainValue);
    }

    public function decrypt($encryptedValue)
    {
        if (false !== strpos($encryptedValue, self::DELIMITER)) {
            list($encryptedValue, $hash) = explode(self::DELIMITER, $encryptedValue);
        };

        return $this->encryptor->decrypt($encryptedValue);
    }

    public static function hash($plainValue)
    {
        if ($plainValue instanceof DateTime) {
            $plainValue = $plainValue->format('Y-m-d H:i:s');
        }

        return hash(self::HASHING_ALGO, $plainValue);
    }

    /**
     * @param $plainValue
     * @param null|string|DateTime $encryptedValue
     * @return string|null
     * @throws \Exception
     */
    public static function pseudonymize($plainValue, $encryptedValue = null)
    {
        $encryptedValue = $encryptedValue ?? RuntimeEncryptor::LABEL_ANONYMIZED;

        if (is_null($plainValue)) {
            return null;
        }

        $encryptedValue = $encryptedValue . self::DELIMITER . self::hash($plainValue);
        return TimeMarker::mark($encryptedValue);
    }

    /**
     * @deprecated
     * @see EncryptedWhereWalker
     */
    public function addWhereConditionToQueryBuilder(QueryBuilder $queryBuilder, $pathExpression, $firstnameValue)
    {
        $paramName = (Slugify::create(['separator' => '_']))->slugify($pathExpression) . '_hash';
        $queryBuilder
            ->andWhere($pathExpression . ' LIKE :' . $paramName)
            ->setParameter($paramName, '%' . self::DELIMITER . self::hash($firstnameValue) . '%');
    }
}
