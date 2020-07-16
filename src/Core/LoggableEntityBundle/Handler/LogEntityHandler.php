<?php
namespace Core\LoggableEntityBundle\Handler;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Core\LoggableEntityBundle\Interfaces\ExtraDataAwareLogEntity;
use Core\LoggableEntityBundle\Interfaces\LogEntityInterface;
use Core\LoggableEntityBundle\Model\LogExtraDataAware;

/**
 * Class LogEntityHandler
 * @package Core\LoggableEntityBundle\Handler
 */
class LogEntityHandler extends AbstractEntityHandler
{
    /**
     * @param LogEntityInterface $log
     * @param $object
     * @param TokenInterface $token
     */
    public function handle(LogEntityInterface $log, $object, TokenInterface $token = null)
    {
        if ($object instanceof LogExtraDataAware && $log instanceof ExtraDataAwareLogEntity && null !== $object->getLogExtraData()) {
            $log->setComment($object->getLogExtraData()->comment);
            $log->setCustomAction($object->getLogExtraData()->customAction);
            $log->setExtraData($object->getLogExtraData()->extraData);
        }

        if(null !== $log->getData()) {
            if (array_key_exists('_extra_data', $log->getData())) {
                $log->setData(array_diff_assoc($log->getData(), array('_extra_data' => true,)));
            }
        }

        if(null !== $token) {
            $log->setUsername($token->getUsername());
        }
    }
}