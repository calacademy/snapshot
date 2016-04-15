<?php

$includePath = dirname(__FILE__) . '/../../include/php/';
require_once($includePath . 'DatabaseUtil.php');
require_once($includePath . 'StringUtil.php');
require_once('Sms.php');

class SmsCallback extends Sms {
	private $_db;

	public function __construct () {
		parent::__construct();

		$db = new DatabaseUtil('snapshot');
		
		$this->_db = $db->getConnection();

		if (!$this->_db) {
			$this->_error('Database connection error');
		}
	}

	public function isValidCode ($myCode) {
		parse_str(file_get_contents('../code/code.txt'));
	    $is_numeric = ($is_numeric === 'true') ? true : false;

	    if (isset($code)) {
	    	// bypass
	    	if ($code == 'bypass') return true;

	    	if (!empty($code) && isset($myCode)) {
	            $body = strtolower(trim($myCode));

	            if ($is_numeric) {
	                // strip everything except integers
	                $body = preg_replace('/[^0-9]/', '', $body);
	            } else {
	                // strip everything except letters
	                $body = preg_replace('/[^a-z]/', '', $body);
	            }

	    		if (strtolower(trim($code)) == $body) {
	    			// correct code, don't send a response
	                return true;
	    		}
	    	}
	    }

	    return false;
	} 

	public function isLocked ($num) {
		$num = mysql_real_escape_string($num);
		$query = "SELECT locked FROM cams WHERE num = '$num'";

		$result = $this->_getDBResource($query);
		$row = mysql_fetch_assoc($result);

		return $row['locked'];
	}

	public function unlock ($num) {
		$num = mysql_real_escape_string($num);
		$query = "UPDATE cams SET locked = 0 WHERE num = '$num'";

		if ($this->_getDBResource($query)) {
			return true;
		} else {
			return false;
		}
	}

	public function isFromTwilio () {
		$agent = strtolower($_SERVER['HTTP_USER_AGENT']);
		return (strpos($agent, 'twilio') !== false);
	}

	private function _error ($str = 'unknown error') {
		$this->json(array(
			'error' => $str
		));
	}

	private function _getDBResource ($query) {
		$resource = mysql_query($query, $this->_db);

		if (!$resource) {
			$this->_error('Database error');
			return false;
		} else {
			return $resource;
		}
	}

	public function received ($data) {
		if (!$this->isFromTwilio()) {
			$this->_error('blocked recipient');
			return false;
		}

		$data = StringUtil::getCleanArray($data);
		$locked = $this->isLocked($data['To']);
		$isValidCode = $this->isValidCode($data['Body']);
		$triggerShutter = !$locked && $isValidCode;
		$triggerShutterString = $triggerShutter ? '0' : '1';

		if ($triggerShutter) {
			$query = "UPDATE cams SET locked = 1 WHERE num = '{$data['To']}'";
			$this->_getDBResource($query);
		}

		$query = "INSERT INTO sms_received
			(
				smssid,
				locked,
				num_to,
				num_from,
				body,
				status
			)
			VALUES
			(
				'{$data['SmsSid']}',
				{$triggerShutterString},
				'{$data['To']}',
				'{$data['From']}',
				'{$data['Body']}',
				'{$data['SmsStatus']}'
			)";

		if ($this->_getDBResource($query)) {
			return array(
				'locked' => $locked,
				'correct_code' => $isValidCode
			);
		} else {
			return false;
		}
	}

	public function callback ($data) {
		if (!$this->isFromTwilio()) {
			$this->_error();
			return false;
		}

		$data = StringUtil::getCleanArray($data);

		$query = "INSERT INTO sms_sent
			(
				smssid,
				num_to,
				num_from,
				status
			)
			VALUES
			(
				'{$data['SmsSid']}',
				'{$data['To']}',
				'{$data['From']}',
				'{$data['SmsStatus']}'
			)";

		if ($this->_getDBResource($query)) {
			return true;
		} else {
			return false;
		}
	}

	public function getStatusesForId ($id) {
		$id = mysql_real_escape_string($id);
		$query = "SELECT status, callback FROM sms_sent WHERE smssid = '{$id}' LIMIT 25";

		$arr = array();

		$result = $this->_getDBResource($query);

		while ($row = mysql_fetch_assoc($result)) {
			$arr[] = $row;
		}

		$this->json($arr);
	}

	public function getShutterRequests ($timeString) {
		$query = "SELECT uid_sms, smssid, num_from, body, callback FROM sms_received WHERE locked = 0 ORDER BY uid_sms DESC LIMIT 1";
		$arr = array();

		$result = $this->_getDBResource($query);

		while ($row = mysql_fetch_assoc($result)) {
			$arr[] = $row;
		}

		$this->json($arr);
	}
}

?>
