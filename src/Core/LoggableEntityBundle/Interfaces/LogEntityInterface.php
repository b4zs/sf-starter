<?php

namespace Core\LoggableEntityBundle\Interfaces;

/**
 * Interface LogEntityInterface
 * @package Core\LoggableEntityBundle\Entity
 */
interface LogEntityInterface
{
    /**
     * Get id
     *
     * @return integer
     */
    public function getId();

    /**
     * Get action
     *
     * @return string
     */
    public function getAction();

    /**
     * Set action
     *
     * @param string $action
     */
    public function setAction($action);

    /**
     * Get object class
     *
     * @return string
     */
    public function getObjectClass();

    /**
     * Set object class
     *
     * @param string $objectClass
     */
    public function setObjectClass($objectClass);

    /**
     * Get object id
     *
     * @return string
     */
    public function getObjectId();

    /**
     * Set object id
     *
     * @param string $objectId
     */
    public function setObjectId($objectId);

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername();

    /**
     * Set username
     *
     * @param string $username
     */
    public function setUsername($username);

    /**
     * Get loggedAt
     *
     * @return \DateTime
     */
    public function getLoggedAt();

    /**
     * @param \DateTime|null $value
     * @return mixed
     */
    public function setLoggedAt(\DateTime $value = null);

    /**
     * Get data
     *
     * @return array
     */
    public function getData();

    /**
     * Set data
     *
     * @param array $data
     */
    public function setData($data);

    /**
     * Set current version
     *
     * @param integer $version
     */
    public function setVersion($version);

    /**
     * Get current version
     *
     * @return integer
     */
    public function getVersion();
}