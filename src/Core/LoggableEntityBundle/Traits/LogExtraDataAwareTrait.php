<?php
namespace Core\LoggableEntityBundle\Traits;

use Core\LoggableEntityBundle\Model\LogExtraData;

/**
 * Class LogExtraDataAwareTrait
 * @package Core\LoggableEntityBundle\Traits
 */
trait LogExtraDataAwareTrait
{
    /**
     * @var LogExtraData
     */
    private $log_extra_data;

    /**
     * @var \DateTime
     */
    private $updated_at;

    /**
     * @return LogExtraData
     */
    public function getLogExtraData()
    {
        return $this->log_extra_data;
    }

    /**
     * @param LogExtraData $logExtraData
     */
    public function setLogExtraData(LogExtraData $logExtraData)
    {
        $this->log_extra_data = $logExtraData;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

    /**
     * @param \DateTime $updated_at
     */
    public function setUpdatedAt(\DateTime $updated_at)
    {
        $this->updated_at = $updated_at;
    }
}