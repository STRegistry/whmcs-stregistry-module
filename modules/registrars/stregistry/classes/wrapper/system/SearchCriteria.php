<?php

class SearchCriteria
{	
	/**
	 * Array containing search conditions
	 * 
	 * @var array
	 */
	private $_criteria = array(
		'field' => array(),
		'op'	=> 'and',
	);

	/**
	 * Last field being set for comparision method
	 * 
	 * @var str
	 */
	private $_lastField = '';

	public function __construct($op = 'and')
	{
		$this->_criteria['op'] = $op;
	}

	/**
	 * Magick
	 * 
	 * @param str $field field name for next comparison function
	 * 
	 * @return SearchCriteria
	 */
	public function __get($field) 
	{
		$this->_lastField = $field;

		return $this;
	}

	/**
	 * Add "=" condition to search criteria
	 * 
	 * @param mixed $value Value can be simple type like str or int or array of values
	 * 
	 * @return SearchCriteria
	 */
	public function equal($value)
	{
		return $this->addCriteria($this->_lastField, 'eq', $value);
	}

	/**
	 * Add "!=" condition to search criteria
	 * 
	 * @param mixed $value Value can be simple type like str or int or array of values
	 * 
	 * @return SearchCriteria
	 */
	public function notEqual($value)
	{
		return $this->addCriteria($this->_lastField, 'neq', $value);
	}

	/**
	 * Add "like" condition to search criteria
	 * 
	 * @param str $value pattern for sql like command
	 * 
	 * @return SearchCriteria
	 */
	public function like($value)
	{
		return $this->addCriteria($this->_lastField, 'like', $value);
	}

	/**
	 * Add "not like" condition to search criteria
	 * 
	 * @param str $value pattern for sql like command
	 * 
	 * @return SearchCriteria
	 */
	public function notLike($value)
	{
		return $this->addCriteria($this->_lastField, 'nlike', $value);
	}

	/**
	 * Add ">" condition to search criteria
	 * 
	 * @param str $value 
	 * 
	 * @return SearchCriteria
	 */
	public function graterThan($value)
	{
		return $this->addCriteria($this->_lastField, 'gt', $value);
	}

	/**
	 * Add "<" condition to search criteria
	 * 
	 * @param str $value 
	 * 
	 * @return SearchCriteria
	 */
	public function lowerThan($value)
	{
		return $this->addCriteria($this->_lastField, 'lt', $value);
	}

	/**
	 * Add ">=" condition to search criteria
	 * 
	 * @param str $value 
	 * 
	 * @return SearchCriteria
	 */
	public function graterThanEqual($value)
	{
		return $this->addCriteria($this->_lastField, 'gte', $value);
	}

	/**
	 * Add "<=" condition to search criteria
	 * 
	 * @param str $value 
	 * 
	 * @return SearchCriteria
	 */
	public function lowerThanEqual($value)
	{
		return $this->addCriteria($this->_lastField, 'lte', $value);
	}

	/**
	 * Add criteria to search condition
	 * 
	 * @param $field field name
	 * @param $function comparison function 
	 * @param $value search value
	 * 
	 * @return SearchCriteria
	 */
	private function addCriteria($field, $function, $value)
	{
		$this->_criteria['field'][$field] = array(
			$function => $value,
		);

		return $this;
	}

	public function getCriteria()
	{
		return $this->_criteria;
	}
}