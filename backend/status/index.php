<?php

	if (!isset($_REQUEST['id'])) {
		die('error');
	}

	require_once('../classes/SmsCallback.php');

	$foo = new SmsCallback();
	$foo->getStatusesForId($_REQUEST['id']);

?>
