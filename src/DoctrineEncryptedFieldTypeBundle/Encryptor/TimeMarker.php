<?php

namespace DoctrineEncryptedFieldTypeBundle\Encryptor;

class TimeMarker implements EncryptorInterface
{
    /** @var EncryptorInterface */
    private $encryptor;

    const DELIMITER = '.time=';

    public function __construct(EncryptorInterface $encryptor)
    {
        $this->encryptor = $encryptor;
    }

    public function encrypt($plainValue)
    {
        if (null === $plainValue) {
            return $plainValue;
        }

        if ($this->isValueEncrypted($plainValue)) {
            return $plainValue;
        }

        return self::mark($this->encryptor->encrypt($plainValue));
    }

    public function decrypt($encryptedValue)
    {
        if (null === $encryptedValue) {
            return $encryptedValue;
        }

        if ($this->isValueEncrypted($encryptedValue)) {
            list($encryptedValue, $marker) = explode(self::DELIMITER, $encryptedValue);
//            var_dump(date('Y-m-d H:i:s', hexdec($marker)));
        } else {
            return $encryptedValue;
        }

        return $this->encryptor->decrypt($encryptedValue);
    }

    public static function mark($encryptedValue)
    {
        return $encryptedValue . self::DELIMITER . dechex(time());
    }

    private function isValueEncrypted($value): bool
    {
        return false !== strpos($value, self::DELIMITER);
    }
}
