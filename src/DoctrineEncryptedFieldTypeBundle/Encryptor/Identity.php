<?php

namespace DoctrineEncryptedFieldTypeBundle\Encryptor;

class Identity implements EncryptorInterface
{
    public function encrypt($plainValue)
    {
        return $plainValue;
    }

    public function decrypt($encryptedValue)
    {
        return $encryptedValue;
    }
}
