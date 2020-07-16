<?php


namespace Core\LoggableEntityBundle\Entity;

use Core\LoggableEntityBundle\Interfaces\ExtraDataAwareLogEntity;
use Core\LoggableEntityBundle\Interfaces\LogEntityInterface;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Loggable\Entity\MappedSuperclass\AbstractLogEntry;
use Symfony\Component\Yaml\Yaml;

/**
 * Gedmo\Loggable\Entity\LogEntry
 *
 * @ORM\Table(
 *     name="log_entries",
 *  indexes={
 *      @ORM\Index(name="log_class_lookup_idx", columns={"object_class"}),
 *      @ORM\Index(name="log_date_lookup_idx", columns={"logged_at"}),
 *      @ORM\Index(name="log_user_lookup_idx", columns={"username"}),
 *      @ORM\Index(name="log_version_lookup_idx", columns={"object_id", "object_class", "version"})
 *  }
 * )
 * @ORM\Entity(repositoryClass="Gedmo\Loggable\Entity\Repository\LogEntryRepository")
 */
class LogEntry extends AbstractLogEntry implements LogEntityInterface, ExtraDataAwareLogEntity
{

	/**
	 * @var string
	 * @ORM\Column(name="comment", type="text", nullable=true)
	 */
	private $comment;

	/**
	 * @var string
	 * @ORM\Column(name="custom_action", type="string", length=255, nullable=true)
	 */
	private $customAction;

	/**
	 * @var string
	 * @ORM\Column(name="extra_data", type="json_array", nullable=true)
	 */
	private $extraData = array();

	/**
	 * @return string
	 */
	public function getComment()
	{
		return $this->comment;
	}

	/**
	 * @param string $comment
	 */
	public function setComment($comment)
	{
		$this->comment = $comment;
	}

	/**
	 * @return string
	 */
	public function getCustomAction()
	{
		return $this->customAction;
	}

	/**
	 * @param string $customAction
	 */
	public function setCustomAction($customAction)
	{
		$this->customAction = $customAction;
	}

	/**
	 * @return string
	 */
	public function getExtraData()
	{
		return $this->extraData;
	}

	/**
	 * @param string $extraData
	 */
	public function setExtraData($extraData)
	{
		$this->extraData = $extraData;
	}

	public function setLoggedAt(\DateTime $value = null)
	{
		if (null === $value) {
			$value = new \DateTime();
		}
		$this->loggedAt = $value;
	}

	public function getNormalizedData()
	{
		return is_array($this->getData()) ? $this->mapData($this->getData()) : null;
	}

	public function getNormalizedDataSerialized()
	{
		return json_encode($this->getNormalizedData());
	}

	private function shortenClass($getObjectClass)
	{
		$a = explode('\\', $getObjectClass);
		return trim(end($a));
	}

	private function mapData(array $input)
	{
		$result = array();
		foreach ($input as $field => $value) {

			$this->stripValue($value);

			$fieldName = $this->shortenClass($field);
			if (in_array($fieldName, array('content', 'settings'))) {
				$value = substr(json_encode($value), 0, 100).'...';
			} elseif (is_array($value)) {
				$value = $this->mapData($value);
			} elseif (is_bool($value)) {
				$value = json_encode($value);
			}

			$result[$fieldName] = $value;
		}
		return $result;
	}

	private function stripValue(&$value)
	{
		if(is_array($value)){
			foreach($value as $key => $val){
				if(is_string($val)){
					$val = strip_tags($val);
					$val = preg_replace('/\s+/S', " ", $val);
					$value[$key] = $val;
				}

				if(is_array($val)){
					$this->stripValue($val);
				}

			}
		}

	}

}
