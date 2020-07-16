<?php
namespace Core\LoggableEntityBundle\Handler;

use Core\LoggableEntityBundle\Interfaces\LogEntityInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * Class DefaultGedmoEntryHandler
 * @package Core\LoggableEntityBundle\Handler
 */
class DefaultGedmoEntryHandler extends AbstractEntityHandler
{

    /**
     * nothing to do here
     *
     * @param LogEntityInterface $log
     * @param $object
     * @param TokenInterface $token
     */
    public function handle(LogEntityInterface $log, $object, TokenInterface $token)
    {
    }
}