<?php
namespace Core\LoggableEntityBundle\Interfaces;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Interface LogEntityTypeHandlerInterface
 * @package Core\LoggableEntityBundle\Interfaces
 */
interface LogEntityTypeHandlerInterface
{

    /**
     * @return string
     */
    public function getHandledEntityClass();

    public function handle(LogEntityInterface $log, $object, TokenInterface $token);

    public function getLogsForSubject(EntityManagerInterface $em, $subjectClass, $subjectId);
}