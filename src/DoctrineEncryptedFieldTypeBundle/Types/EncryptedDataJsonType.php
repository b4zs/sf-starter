<?php

namespace DoctrineEncryptedFieldTypeBundle\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;

final class EncryptedDataJsonType extends EncryptedDataType
{
    const NAME = 'encrypted_data_json';

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        $result = parent::convertToPHPValue($value, $platform);
        $result = json_decode($result, true);

        return $result;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        $value = json_encode($value);
        $result = parent::convertToDatabaseValue($value, $platform);


        return $result;
    }
}
