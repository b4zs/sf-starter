<?php
namespace Core\LoggableEntityBundle\Handler;

use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Loggable\Entity\LogEntry;
use Core\LoggableEntityBundle\Exceptions\EntityChangeLogServiceException;
use Core\LoggableEntityBundle\Interfaces\LogEntityInterface;
use Core\LoggableEntityBundle\Interfaces\LogEntityTypeHandlerInterface;

/**
 * Class AbstractEntityHandler
 * @package Core\LoggableEntityBundle\Handler
 */
abstract class AbstractEntityHandler implements LogEntityTypeHandlerInterface
{
    /**
     * @var string
     */
    protected $handledClass;

    /**
     * @param $handledClass
     * @throws EntityChangeLogServiceException
     */
    public function __construct($handledClass) {
        $class = new $handledClass();
        if(!$class instanceof LogEntityInterface && !$class instanceof LogEntry) {
            throw new EntityChangeLogServiceException('The log entity must implement the LogEntityInterface');
        }
        $this->handledClass = $handledClass;
    }

    /**
     * @return string
     */
    public function getHandledEntityClass() {
        return $this->handledClass;
    }

    /**
     * @param EntityManagerInterface $em
     * @param $subjectClass
     * @param $subjectId
     * @return LogEntityInterface[]
     */
    public function getLogsForSubject(EntityManagerInterface $em, $subjectClass, $subjectId) {
        $repository = $em->getRepository($this->handledClass);

        return $repository->findBy(array('objectClass' => $subjectClass, 'objectId' => $subjectId), array('loggedAt' => 'DESC'));
    }
}