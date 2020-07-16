<?php

namespace Core\ClassificationBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;


class ValidationOverrideCompilerPass implements CompilerPassInterface{

    public function process(ContainerBuilder $container)
    {

        $validatorBuilder = $container->getDefinition('validator.builder');
        $methodCalls = $validatorBuilder->getMethodCalls();
        foreach ($methodCalls as $methodCallIx => $methodCall) {
            if ('addXmlMappings' == $methodCalls[$methodCallIx][0]) {
                foreach ($methodCalls[$methodCallIx][1][0] as $argumentIx => $argument) {
                    if (preg_match('/\/sonata-project\/classification-bundle\/Resources\/config\/validation\.xml/', $argument)) {
                        unset($methodCalls[$methodCallIx][1][0][$argumentIx]);
                    }
                }
                $methodCalls[$methodCallIx][1][0] = array_values($methodCalls[$methodCallIx][1][0]);
                break;
            }
        }
        $validatorBuilder->setMethodCalls($methodCalls);
    }
}

