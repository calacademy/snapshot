<?php 
	
	header('Cache-Control: no-cache, must-revalidate');
	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
	header('Content-type: application/json');

	require('../classes/SendError.php');
	$foo = new SendError();
	$foo->send($_REQUEST['num']);

	die(json_encode(array(
		'success' => 1,
		'recipient' => $_REQUEST['num']
	)));

?>
