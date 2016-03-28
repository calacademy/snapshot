<?php
	
	require_once('../classes/Sms.php');
    $foo = new Sms();

	$file = 'code.txt';

	if (isset($_REQUEST['c'])) {
		if (!empty($_REQUEST['c'])) {
			file_put_contents($file, trim($_REQUEST['c']));
		}
	}

	$foo->json(array(
		'code' => trim(file_get_contents($file))
	));

?>
