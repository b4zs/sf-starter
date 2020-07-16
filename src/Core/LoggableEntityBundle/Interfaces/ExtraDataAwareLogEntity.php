<?php
namespace Core\LoggableEntityBundle\Interfaces;

/**
 * Interface ExtraDataAwareLogEntity
 * @package Core\LoggableEntityBundle\Interfaces
 */
interface ExtraDataAwareLogEntity
{
    /**
     * @return string
     */
    public function getComment();

    /**
     * @param string $comment
     */
    public function setComment($comment);

    /**
     * @return string
     */
    public function getCustomAction();

    /**
     * @param string $customAction
     */
    public function setCustomAction($customAction);

    /**
     * @return string
     */
    public function getExtraData();

    /**
     * @param string $extraData
     */
    public function setExtraData($extraData);
}