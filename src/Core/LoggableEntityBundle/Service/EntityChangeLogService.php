<?php
namespace Core\LoggableEntityBundle\Service;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Core\LoggableEntityBundle\Exceptions\EntityChangeLogServiceException;
use Core\LoggableEntityBundle\Interfaces\LogEntityInterface;
use Core\LoggableEntityBundle\Interfaces\LogEntityTypeHandlerInterface;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\PersistentCollection;

/**
 * Class EntityChangeLogService
 * @package Core\LoggableEntityBundle\Service
 */
class EntityChangeLogService
{
    /**
     * @var LogEntityTypeHandlerInterface[]
     */
    protected $handlers;

    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @param TokenStorageInterface $tokenStorageInterface
     */
    public function setTokenStorage(TokenStorageInterface $tokenStorageInterface) {
        $this->tokenStorage = $tokenStorageInterface;
    }

    /**
     * @param array $handlers
     */
    public function setHandlers(array $handlers = array()) {
        $this->handlers = array();
        foreach($handlers as $handler) {
            $this->addHandler($handler);
        }
    }

    /**
     * @param LogEntityTypeHandlerInterface $handler
     */
    public function addHandler(LogEntityTypeHandlerInterface $handler) {
        $this->handlers[$handler->getHandledEntityClass()] = $handler;
    }

    public function handle(LogEntityInterface $log, $object) {

        $logClass = get_class($log);

        if(!array_key_exists($logClass, $this->handlers)) {
            throw new EntityChangeLogServiceException('No handler for class: ' . $logClass);
        }

        $this->handlers[$logClass]->handle($log, $object, $this->tokenStorage->getToken());
    }

    /**
     * @param EntityManagerInterface $em
     * @param string $subjectClass
     * @param string $subjectId
     * @param bool|false $relatedObjects
     * @return array
     */
    public function getLogEntries(EntityManagerInterface $em, $subjectClass, $subjectId, $relatedObjects = false) {
        $logEntries = array();

        foreach($this->handlers as $handler) {
            $logEntries = array_merge($logEntries, $handler->getLogsForSubject($em, $subjectClass, $subjectId));
        }

        if($relatedObjects) {
            $logEntries = array_merge($logEntries, $this->getRelatedLogObjects($em, $subjectClass, $subjectId));
        }

        usort($logEntries, function(LogEntityInterface $a, LogEntityInterface $b) {
            if($a->getLoggedAt()->getTimestamp() < $b->getLoggedAt()->getTimestamp()) {
                return 1;
            }

            return -1;
        });

        return $logEntries;
    }

    /**
     * TODO: ezt kicsit lehetne refaktolni
     *
     * @param EntityManagerInterface $em
     * @param $subjectClass
     * @param $subjectId
     * @return array
     */
    protected function getRelatedLogObjects(EntityManagerInterface $em, $subjectClass, $subjectId) {

        $subject = $em->find($subjectClass, $subjectId);

        $relatedLogs = array();

        $classMeta = $em->getClassMetadata($subjectClass);

        $maping = $classMeta->getAssociationMappings();

        foreach($maping as $fieldMapping) {
            $fieldValue = $classMeta->getFieldValue($subject, $fieldMapping['fieldName']);
            $fieldMeta = $em->getClassMetadata($fieldMapping['targetEntity']);
            $identifyers = $fieldMeta->getIdentifier();
            $id = $identifyers[0];
            $functionName = 'get' . ucfirst($id);
            if($fieldValue instanceof PersistentCollection) {
                if($fieldValue->count() > 0) {
                    foreach($fieldValue as $currentItem) {
                        $idValue = $currentItem->{$functionName}();
                        $relatedLogs = array_merge($relatedLogs, $this->getLogEntries($em, $fieldMapping['targetEntity'], $idValue, false));
                    }
                }
            } else {
                if(is_object($fieldValue)) {
                    $idValue = $fieldValue->{$functionName}();
                    $relatedLogs = array_merge($relatedLogs, $this->getLogEntries($em, $fieldMapping['targetEntity'], $idValue, false));
                }
            }
        }

        return $relatedLogs;
    }

    /**
     * @return string
     */
    public function getLoggableValue($subject) {

        if(is_array($subject)) {
            return $this->getArrayData($subject);
        }

        $class = ClassUtils::getRealClass(get_class($subject));

        switch($class){
            case 'DateTime':
                return $subject->format('Y-m-d H:i:s');
                break;
            case 'Doctrine\Common\Collections\Collection':
                return $subject->toArray();
                break;
            case 'Application\UserBundle\Entity\User':
                return (string)$subject;
                break;
            case 'Core\ClassificationBundle\Entity\Category':
                return $subject->getName();
                break;
            default:
                return $this->getObjectData($subject);
                break;
        }
    }

    /**
     * @param object $subject
     * @return string
     */
    protected function getObjectData($subject) {

        $reflectionClass = new \ReflectionClass($subject);
        $ret = array();

        foreach($reflectionClass->getProperties() as $prop) {
            $prop->setAccessible(true);
            $val = $prop->getValue($subject);
            if(null !== $val && $val != '') {
                if(!is_object($val) && !is_array($val)) {
                    $ret[$reflectionClass->name][$prop->name] = $val;
                }
            }
        }

        return $ret;
    }
}