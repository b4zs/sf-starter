<?php

namespace Core\LoggableEntityBundle\Twig;

use Core\LoggableEntityBundle\Entity\LogEntry;

class LoggableTwigExtension extends \Twig_Extension
{
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('is_string', 'is_string'),
            new \Twig_SimpleFunction('serialize_loggable_entity', array($this, 'serializeLoggableEntity')),
        );
    }

    public function getName()
    {
        return 'loggable_twig_ext';
    }

    public function serializeLoggableEntity(LogEntry $object)
    {
        return array(
            'id'           => $object->getId(),
            'object_class' => $this->shortenClass($object->getObjectClass()),
            'object_id'    => $object->getId(),
            'data'         => $object->getNormalizedData(),
            'action'       => $object->getAction(),
            'user'         => $object->getUsername(),
        );
    }

    private function shortenClass($getObjectClass)
    {
        $a = explode('\\', $getObjectClass);
        return trim(end($a));
    }
}
