<?php

namespace DoctrineEncryptedFieldTypeBundle\Encryptor;

use DoctrineEncryptedFieldTypeBundle\Exception\InvalidEncryptedValueException;
use Symfony\Contracts\Translation\TranslatorInterface;

class RuntimeEncryptor implements EncryptorInterface
{
    const LABEL_ANONYMIZED = 'anonymized';

    /** @var EncryptorInterface */
    private $currentEncryptor;

    /** @var TranslatorInterface */
    private $translator;

    public function __construct(EncryptorInterface $finalEncryptor, TranslatorInterface $translator)
    {
        $this->currentEncryptor = $finalEncryptor;
        $this->translator = $translator;
    }

    public function encrypt($plainValue)
    {
        return $this->currentEncryptor->encrypt($plainValue);
    }

    public function decrypt($encryptedValue)
    {
        try {
            $decryptedValue = $this->currentEncryptor->decrypt($encryptedValue);
        } catch (InvalidEncryptedValueException $e) {
            $decryptedValue = $this->translator->trans(self::LABEL_ANONYMIZED);
        }

        return $decryptedValue;
    }

    public function setCurrentEncryptor(EncryptorInterface $currentEncryptor)
    {
        $this->currentEncryptor = $currentEncryptor;
    }
}
