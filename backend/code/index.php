<?php
	
	require_once('../classes/Sms.php');
    $foo = new Sms();

	$file = 'code.txt';

	if (isset($_REQUEST['generate'])) {
		if (intval($_REQUEST['generate'])) {
			file_put_contents($file, rand());
		}
	}

	$foo->json(array(
		'code' => trim(file_get_contents($file))
	));

?>
