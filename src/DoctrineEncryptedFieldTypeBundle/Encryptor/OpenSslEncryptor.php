<?php

namespace DoctrineEncryptedFieldTypeBundle\Encryptor;

use DoctrineEncryptedFieldTypeBundle\Exception\InvalidEncryptedValueException;

class OpenSslEncryptor implements EncryptorInterface
{
    private $cipher = 'aes-256-cbc';

    private $key;

    public function __construct($key)
    {
        $this->key = $key;
    }

    public function encrypt($plainValue)
    {
        if (is_null($plainValue) || is_object($plainValue)) {
            return $plainValue;
        }

        if (is_object($plainValue)) {
            throw new \InvalidArgumentException('You cannot encrypt an object.', get_class($plainValue));
        }

        // Create a cipher of the appropriate length for this method.
        $ivsize = openssl_cipher_iv_length($this->cipher);
        $iv     = openssl_random_pseudo_bytes($ivsize);

        $encryptedValue = openssl_encrypt(
            $plainValue,
            $this->cipher,
            $this->key,
            0,
            $iv
        );

        $encryptedValue = base64_encode($iv . $encryptedValue);

        return $encryptedValue;
    }

    public function decrypt($encryptedValue)
    {
        if ($encryptedValue === null || is_object($encryptedValue) || 'anonymized' === $encryptedValue) {
            return $encryptedValue;
        }

        $encryptedValue = base64_decode($encryptedValue);

        $ivsize     = openssl_cipher_iv_length($this->cipher);
        $iv         = mb_substr($encryptedValue, 0, $ivsize, '8bit');
        $ciphertext = mb_substr($encryptedValue, $ivsize, null, '8bit');

        try {
            $decryptedValue = openssl_decrypt(
                $ciphertext,
                $this->cipher,
                $this->key,
                0,
                $iv
            );
        } catch (\ErrorException $e) {
            throw new InvalidEncryptedValueException(openssl_error_string());
        }

        return $decryptedValue;
    }
}
