<?php

require_once dirname(__FILE__) . '/Model.php';
require_once dirname(__FILE__) . '/../system/SearchCriteria.php';

class Poll extends Model
{
	public static function getInstance($class = __CLASS__)
	{
		return parent::getInstance($class);
	}

	/**
	 * Return list of unread notifications from regitry
	 * 
	 * @param int $limit 
	 * @param int $offset
	 * 
	 * @return str json response
	 */
	public function request($limit = 100, $offset = 0, $cltrid = false)
	{
		$criteria = new SearchCriteria();
		$criteria->ntDate->equal('null');

		return $this->search($criteria, $limit, $offset, array('crDate' => 'desc'), $cltrid);
	}

	/**
	 * Mark notification as read
	 * 
	 * @param int $messageId Message id recieved by request call
	 * 
	 * @return str json response
	 */
	public function ack($messageId, $cltrid = false)
	{
		$json = APIRequest::POST(sprintf("/notifications/%d/read", $messageId), $cltrid ?: APIRequest::defaultClientTransactionID(), STRegistry::Session()->getAuthToken());

		return $json;
	}

	/**
	 * Make search over registrar notifications collection
	 * 
	 * @param SearchCriteria $criteria  Prepared search filters
	 * @param int $limit results limit	
	 * @param int $offset start rowset from
	 * @param array $sort rowset sort rule array('field' => 'asc|desc')
	 * 
	 * @return str json response
	 */
	public function search(SearchCriteria $criteria, $limit = 100, $offset = 0, array $sort = array(), $cltrid = false)
	{
		$get = $criteria->getCriteria();
		$get['do']     = 'search';
		$get['limit']  = $limit;
		$get['offset'] = $offset;

		foreach ($sort as $field => $direction) {
			$get['sort_field']     = $field;
			$get['sort_direction'] = $direction;
			// sorry. just one field
			break;
		}
		
		$json = APIRequest::GET('/notifications/', $cltrid ?: APIRequest::defaultClientTransactionID(), STRegistry::Session()->getAuthToken(), $get);

		return $json;
 	}
}