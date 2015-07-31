<?php

abstract class BaseObject
{
	/**
	 * @var str
	 */
	private $_validationErrorMessage = '';

	/**
	 * @var int
	 */
	private $_validationErrorCode    = 0;

	/**
	 * This method must be overrided by class childs
	 * 
	 * @return  false
	 */
	public function validate()
	{
		return false;
	}

	/**
	 * Return last validation error code
	 * 
	 * @return int
	 */
	public function getValidationErrorCode()
	{
		return $this->_validationErrorCode;
	}

	/**
	 * Return last validation error message
	 * 
	 * @return str
	 */
	public function getValidationErrorMessage()
	{
		return $this->_validationErrorMessage;
	}

	/**
	 * Set validation error code
	 * 
	 * @param int $code error code
	 * @return BaseObject
	 */
	protected function setValidationErrorCode($code)
	{
		$this->_validationErrorCode = $code;

		return $this;
	}

	/**
	 * Set validation error message
	 * 
	 * @param str $message error message
	 * @return BaseObject
	 */
	protected function setValidationErrorMessage($message) 
	{
		$this->_validationErrorMessage = $message;

		return $this;
	}

	/**
	 * Format specified date using $format or return it timestamp
	 * 
	 * @param mixed $date formated date or timestamp
	 * @param str $format date function suitable date format
	 * 
	 * @return mixed formated date or it timestamp
	 */
	protected function dateFormat($date, $format = false)
	{
		if (!is_numeric($date)) {
			$date = strtotime($date);
		}
		if (!empty($format)) {
			$date = date($format, $date);
		}

		return $date;
	}
}