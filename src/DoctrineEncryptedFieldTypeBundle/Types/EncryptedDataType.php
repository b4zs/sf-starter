<?php

namespace DoctrineEncryptedFieldTypeBundle\Types;

use DoctrineEncryptedFieldTypeBundle\Encryptor\Identity;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\Type;

abstract class EncryptedDataType extends Type
{
    const NAME = 'encrypted_data';

    private static $encryptor;

    /**
     * @param array $fieldDeclaration
     * @param AbstractPlatform $platform
     *
     * @return string
     */
    public function getSqlDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
    {
        if (isset($fieldDeclaration['length'])) {
            $fieldDeclaration['length'] *= 10;
        }

        return $platform->getClobTypeDeclarationSQL($fieldDeclaration);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if (null === $value) {
            return null;
        }

        $decrypted = $this->getEncryptor()->decrypt($value);

        // TODO: type conversion!
        return $decrypted;
    }

    /**
     * @param PersonalData $value
     * @param AbstractPlatform $platform
     * @return mixed|null|string
     * @throws ConversionException
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if (empty($value)) {
            return null;
        }

        switch (true) {
            case is_string($value):
                $stringValue = $value;
                break;
            default:
                throw ConversionException::conversionFailed($value, self::NAME);
        }

        if (is_string($stringValue)) {
            $encrypted = $this->getEncryptor()->encrypt($stringValue);
            return $encrypted;
        }

        throw ConversionException::conversionFailed($value, self::NAME);
    }

    /**
     * @param AbstractPlatform $platform
     *
     * @return bool
     */
    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return true;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return static::NAME;
    }

    private function getEncryptor()
    {
        if (null === self::$encryptor) {
            self::$encryptor = new Identity();
        }
        return self::$encryptor;
    }

    public static function setEncryptor($encryptor)
    {
        self::$encryptor = $encryptor;
    }
}
