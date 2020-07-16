<?php
namespace Core\LoggableEntityBundle\EventListener;

use Doctrine\Common\EventArgs;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ReflectionEmbeddedProperty;
use Doctrine\ORM\PersistentCollection;
use Doctrine\ORM\UnitOfWork;
use Gedmo\Loggable\Mapping\Event\Adapter\ORM;
use Gedmo\Loggable\Mapping\Event\LoggableAdapter;
use Gedmo\Tool\Wrapper\AbstractWrapper;
use Core\LoggableEntityBundle\Interfaces\LogEntityInterface;
use Core\LoggableEntityBundle\Entity\LogEntry;
use Core\LoggableEntityBundle\Model\LogExtraData;
use Core\LoggableEntityBundle\Model\LogExtraDataAware;
use Core\LoggableEntityBundle\Service\EntityChangeLogService;

/**
 *
 * TODO: refakt a gedmos listenerből áthozott / átírt részeket
 *
 * Class LoggableListener
 * @package Core\LoggableEntityBundle\EventListener
 */
class LoggableListener extends \Gedmo\Loggable\LoggableListener
{

	/**
	 * @var EntityChangeLogService
	 */
	protected $service;

	/**
	 * @param EntityChangeLogService $service
	 */
	public function setService(EntityChangeLogService $service)
	{
		$this->service = $service;
	}

	/**
	 * @param object $logEntry
	 * @param object $object
	 * @throws \Core\LoggableEntityBundle\Exceptions\EntityChangeLogServiceException
	 */
	protected function prePersistLogEntry($logEntry, $object)
	{
		$this->service->handle($logEntry, $object);
	}

	/**
	 * Get the LogEntry class
	 *
	 * @param LoggableAdapter $ea
	 * @param string $class
	 *
	 * @return string
	 */
	protected function getLogEntryClass(LoggableAdapter $ea, $class)
	{
		/** @var ObjectManager $om */
		$om        = $ea->getObjectManager();
		/** @var ClassMetadata $meta */
		$meta      = $om->getClassMetadata($class);
		$config    = $this->getConfiguration($om, $meta->name);
		return $config['logEntryClass'];
	}

	/**
	 * Returns an objects changeset data
	 *
	 * @param LoggableAdapter $ea
	 * @param object $object
	 * @param object $logEntry
	 *
	 * @return array
	 */
	protected function getObjectChangeSetData($ea, $object, $logEntry)
	{
		/** @var EntityManagerInterface $om */
		$om        = $ea->getObjectManager();
		$wrapped   = AbstractWrapper::wrap($object, $om);
		/** @var ClassMetadata $meta */
		$meta      = $wrapped->getMetadata();
		$config    = $this->getConfiguration($om, $meta->name);
		$uow       = $om->getUnitOfWork();
		$newValues = array();

		foreach ($ea->getObjectChangeSet($uow, $object) as $field => $changes) {
			if (empty($config['versioned']) || !$this->isFieldVersioned($object, $field, $config, $changes)) {
				continue;
			}
			$value = $changes[1];
			if ($meta->isSingleValuedAssociation($field) && $value) {
				if ($wrapped->isEmbeddedAssociation($field)) {
					$value = $this->getObjectChangeSetData($ea, $value, $logEntry);
				} else {
					$value = $this->service->getLoggableValue($value);
				}
			}
			$newValues[$field] = $value;
		}

		if ($object instanceof LogExtraDataAware
			&& $object->getLogExtraData() instanceof LogExtraData
			&& $object->getLogExtraData()->hasData()) {
			$newValues['_extra_data'] = true;
		}

		$newValues = array_merge($newValues, $this->getExtraChangeSetData($object));

		return $newValues;
	}

	/**
	 * extra changesetet generálhatunk projekt specifikusan. nem kell felülcsapni így az egészet
	 *
	 * @param $object
	 * @return array
	 */
	protected function getExtraChangeSetData($object) {
		return array();
	}

	/**
	 * @param $object
	 * @param $field
	 * @param $config
	 * @param $changes
	 * @return bool
	 */
	protected function isFieldVersioned($object, $field, $config, $changes)
	{
		return
			(count(array_filter($changes)) > 0)
			&& (
				in_array($field, $config['versioned'])
				|| (false !== strpos($field, '.'))  // this is a hack to let embedded (embeddable) changeset to be logged
			)
		;
	}

	/**
	 * Create a new Log instance
	 * TODO: erősen refakt
	 *
	 * @param string          $action
	 * @param object          $object
	 * @param LoggableAdapter $ea
	 *
	 * @return \Gedmo\Loggable\Entity\MappedSuperclass\AbstractLogEntry|null
	 */
	protected function createLogEntry($action, $object, LoggableAdapter $ea)
	{
		/** @var EntityManagerInterface $om */
		$om = $ea->getObjectManager();
		$wrapped = AbstractWrapper::wrap($object, $om);
		/** @var ClassMetadata $meta */
		$meta = $wrapped->getMetadata();

		if ($config = $this->getConfiguration($om, $meta->name)) {
			$logEntryClass = $this->getLogEntryClass($ea, $meta->name);
			/** @var ClassMetadata $logEntryMeta */
			$logEntryMeta = $om->getClassMetadata($logEntryClass);
			/** @var LogEntry $logEntry */
			$logEntry = $logEntryMeta->newInstance();
			$logEntry->setAction($action);
			$logEntry->setUsername($this->username);
			$logEntry->setObjectClass($meta->name);
			$logEntry->setLoggedAt();

			// check for the availability of the primary key
			$uow = $om->getUnitOfWork();

			if ($action === self::ACTION_CREATE && $ea->isPostInsertGenerator($meta)) {
				$this->pendingLogEntryInserts[spl_object_hash($object)] = $logEntry;
			} else {
				$logEntry->setObjectId($wrapped->getIdentifier());
			}

			$newValues = array();
			if ($action !== self::ACTION_REMOVE && isset($config['versioned'])) {
				$newValues = $this->getObjectChangeSetData($ea, $object, $logEntry);

				foreach($newValues as $key => $val) {
					if(is_object($val)) {
						$newValues[$key] = $this->service->getLoggableValue($val);
					}
				}

				foreach($meta->getReflectionProperties() as $property) {
					if($property instanceof ReflectionEmbeddedProperty) {
						continue;
					}
					$prop = $meta->getReflectionProperty($property->name)->getValue($object);

					if($prop instanceof PersistentCollection && $prop->isDirty()) {
						if(!array_key_exists($property->name, $newValues)) {
							$newValues[$property->name] = array();
						}

						$insertDiff = $prop->getInsertDiff();

						if(!empty($insertDiff)) {
							$newValues[$property->name]['added'] = array();
							foreach($insertDiff as $diff){
								/** @var ClassMetadata $propMeta */
								if(is_object($diff)) {
									$newValues[$property->name]['added'][] = $this->service->getLoggableValue($diff);
								} else {
									$newValues[$property->name]['added'][] = $diff;
								}
							}
						}

						$deleteDiff = $prop->getDeleteDiff();

						if(!empty($deleteDiff)) {
							$newValues[$property->name]['deleted'] = array();
							foreach($deleteDiff as $diff){
								if(is_object($diff)) {
									$newValues[$property->name]['deleted'][] = $this->service->getLoggableValue($diff);
								} else {
									$newValues[$property->name]['deleted'][] = $diff;
								}
							}
						}
					}
				}

				$logEntry->setData($newValues);
			}

			if(($action === self::ACTION_UPDATE && 0 === count($newValues)) && !$object instanceof LogExtraDataAware) {
				return null;
			}

			$version = 1;
			if ($action !== self::ACTION_CREATE) {
				$version = $ea->getNewVersion($logEntryMeta, $object);
				if (empty($version)) {
					// was versioned later
					$version = 1;
				}
			}
			$logEntry->setVersion($version);

			$this->prePersistLogEntry($logEntry, $object);

			$om->persist($logEntry);
			/** @var UnitOfWork $uow */
			$uow->computeChangeSet($logEntryMeta, $logEntry);

			return $logEntry;
		}

		return null;
	}

	/**
	 * Checks for inserted object to update its logEntry
	 * foreign key
	 *
	 * @param EventArgs $args
	 *
	 * @return void
	 */
	public function postPersist(EventArgs $args)
	{
		/** @var ORM $ea */
		$ea = $this->getEventAdapter($args);
		$object = $ea->getObject();
		/** @var EntityManagerInterface $om */
		$om = $ea->getObjectManager();
		$oid = spl_object_hash($object);
		/** @var UnitOfWork $uow */
		$uow = $om->getUnitOfWork();

		if ($this->pendingLogEntryInserts && array_key_exists($oid, $this->pendingLogEntryInserts)) {
			$wrapped = AbstractWrapper::wrap($object, $om);

			$logEntry = $this->pendingLogEntryInserts[$oid];
			$logEntryMeta = $om->getClassMetadata(get_class($logEntry));

			$id = $wrapped->getIdentifier();
			$logEntryMeta->getReflectionProperty('objectId')->setValue($logEntry, $id);
			$uow->scheduleExtraUpdate($logEntry, array(
				'objectId' => array(null, $id),
			));
			$ea->setOriginalObjectProperty($uow, spl_object_hash($logEntry), 'objectId', $id);
			unset($this->pendingLogEntryInserts[$oid]);
		}
		if ($this->pendingRelatedObjects && array_key_exists($oid, $this->pendingRelatedObjects)) {
			$wrapped = AbstractWrapper::wrap($object, $om);
			$identifiers = $wrapped->getIdentifier(false);
			foreach ($this->pendingRelatedObjects[$oid] as $props) {
				/** @var LogEntityInterface $logEntry */
				$logEntry = $props['log'];
				$logEntryMeta = $om->getClassMetadata(get_class($logEntry));
				$oldData = $data = $logEntry->getData();
				$data[$props['field']] = $identifiers;

				$logEntry->setData($data);

				$uow->scheduleExtraUpdate($logEntry, array(
					'data' => array($oldData, $data),
				));
				$ea->setOriginalObjectProperty($uow, spl_object_hash($logEntry), 'data', $data);
			}
			unset($this->pendingRelatedObjects[$oid]);
		}
	}
}