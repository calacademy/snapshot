<?php 

	class Sms {
		public function __construct () {
		}
		
		public function json ($data) {
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
	}

?>
