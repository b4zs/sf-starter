<?php

namespace DoctrineEncryptedFieldTypeBundle\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;

class EncryptedDataDateTimeType extends EncryptedDataType
{
    const NAME = 'encrypted_data_datetime';

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        $string = parent::convertToPHPValue($value, $platform);

        if (is_string($string)) {
            if ($string == 'Törölt adat') {
                return $string;
            }

            $value = new \DateTime($string); // TODO: exception handling
            return $value;
        } else {
            return null;
        }
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if ($value instanceof \DateTime) {
            $value = $value->format('Y-m-d H:i:s');
        }

        return parent::convertToDatabaseValue($value, $platform);
    }
}
