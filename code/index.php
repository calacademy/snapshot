<?php
	
	header('Cache-Control: no-cache, must-revalidate');
	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
	header('Content-type: application/json');

	$file = 'code.txt';

	if (isset($_REQUEST['generate'])) {
		if (intval($_REQUEST['generate'])) {
			file_put_contents($file, rand());
		}
	}

	die(json_encode(array(
		'code' => trim(file_get_contents($file))
	)));

?>
