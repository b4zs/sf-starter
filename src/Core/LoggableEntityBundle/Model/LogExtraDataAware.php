<?php


namespace Core\LoggableEntityBundle\Model;


interface LogExtraDataAware
{
	/**
	 * @return LogExtraData|null
	 */
	public function getLogExtraData();

	/**
	 * @param LogExtraData $logExtraData
	 * @return void
	 */
	public function setLogExtraData(LogExtraData $logExtraData);

	/**
	 * @param \DateTime $dateTime
	 * @return void
	 */
	public function setUpdatedAt(\DateTime $dateTime);
}