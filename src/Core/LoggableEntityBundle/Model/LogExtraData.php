<?php
namespace Core\LoggableEntityBundle\Model;

/**
 * Class LogExtraData
 * @package Core\LoggableEntityBundle\Model
 */
class LogExtraData
{
	/**
	 * @var string
	 */
	public $comment;

	/**
	 * @var string
	 */
	public $customAction;

	/**
	 * @var array
	 */
	public $extraData = array();

	/**
	 * @return bool
	 */
	public function hasData()
	{
		foreach ($this as $v) {
			if (!empty($v)) {
				return true;
			}
		}

		return false;
	}

}