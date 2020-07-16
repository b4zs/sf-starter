<?php

namespace DoctrineEncryptedFieldTypeBundle;

use Doctrine\ORM\Query;
use DoctrineEncryptedFieldTypeBundle\DependencyInjection\CoreDoctrineEncryptedFieldTypeExtension;
use DoctrineEncryptedFieldTypeBundle\Types\EncryptedDataType;
use DoctrineEncryptedFieldTypeBundle\Walker\EncryptedWhereWalker;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class DoctrineEncryptedFieldTypeBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->registerExtension(new CoreDoctrineEncryptedFieldTypeExtension());
    }

    public function boot()
    {
        parent::boot();

        $encryptor = $this->container->get('DoctrineEncryptedFieldTypeBundle\Encryptor\RuntimeEncryptor');
        EncryptedDataType::setEncryptor($encryptor);

        $this->container->get('doctrine.orm.default_entity_manager')->getConfiguration()
            ->setDefaultQueryHint(Query::HINT_CUSTOM_OUTPUT_WALKER, EncryptedWhereWalker::class);
    }
}
