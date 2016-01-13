<?php

$includePath = '/Library/Webserver/Documents/include/php/';
require_once($includePath . 'DatabaseUtil.php');
require_once($includePath . 'StringUtil.php');
// require_once($includePath . 'Validate.php');
// require_once($includePath . 'phpmailer/class.phpmailer.php');

class SmsCallback {
	private $_db;

	public function __construct () {
		$db = new DatabaseUtil('snapshot');
		$this->_db = $db->getConnection();

		if (!$this->_db) {
			$this->_error('Database connection error');
		}
	}

	public function isFromTwilio () {
		$agent = strtolower($_SERVER['HTTP_USER_AGENT']);
		return (strpos($agent, 'twilio') !== false);
	}

	private function _json ($data) {
		header('Access-Control-Allow-Origin: *');
		header('Cache-Control: no-cache, must-revalidate');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		header('Content-type: application/json');

		// prepend callback function if it looks like a JSONP request
		$callback = '';

		if (isset($_REQUEST['callback'])
			&& !empty($_REQUEST['callback'])) {
				$callback = $_REQUEST['callback'];
		}

		if (isset($_REQUEST['jsoncallback'])
			&& !empty($_REQUEST['jsoncallback'])) {
				$callback = $_REQUEST['jsoncallback'];
		}

		// do the encoding
		$data = json_encode($data);

		if (empty($callback)) {
			die($data);
		}

		die($callback . '(' . $data . ');');
	}

	private function _error ($str = 'unknown error') {
		$this->_json(array(
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
			$this->_error();
			return false;
		}

		$data = StringUtil::getCleanArray($data);

		$query = "INSERT INTO sms_received
			(
				smssid,
				num_to,
				num_from,
				body,
				status
			)
			VALUES
			(
				'{$data['SmsSid']}',
				'{$data['To']}',
				'{$data['From']}',
				'{$data['Body']}',
				'{$data['SmsStatus']}'
			)";

		if ($this->_getDBResource($query)) {
			return true;
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

		$this->_json($arr);
	}

	public function getShutterRequests ($timeString) {
		// $query = "SELECT smssid, num_from, body FROM sms_received WHERE callback > DATE_SUB(NOW(), INTERVAL 1 MINUTE) LIMIT 25";
		
		// $time = date('Y-m-d H:i:s', intval($timeString));
		// $query = "SELECT smssid, num_from, body FROM sms_received WHERE callback > '{$time}' ORDER BY callback ASC LIMIT 1";

		$query = "SELECT uid_sms, smssid, num_from, body, callback FROM sms_received ORDER BY uid_sms DESC LIMIT 1";

		$arr = array();

		$result = $this->_getDBResource($query);

		while ($row = mysql_fetch_assoc($result)) {
			$arr[] = $row;
		}

		$this->_json($arr);
	}
}

?>
