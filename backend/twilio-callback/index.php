<?php

	require_once('../classes/SmsCallback.php');

	$foo = new SmsCallback();

	if ($foo->callback($_REQUEST)) {
		die('success');
	} else {
		die('fail');
	}

?>
