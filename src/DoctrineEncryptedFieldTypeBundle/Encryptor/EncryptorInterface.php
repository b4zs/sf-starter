<?php

namespace DoctrineEncryptedFieldTypeBundle\Encryptor;

interface EncryptorInterface
{
    public function encrypt($plainValue);

    public function decrypt($encryptedValue);
}
