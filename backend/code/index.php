<?php
	
	require_once('../classes/SmsCallback.php');
    $foo = new SmsCallback();

    if (isset($_REQUEST['num'])) {
    	$foo->unlock($_REQUEST['num']);	
    }

	$file = 'code.txt';

	if (isset($_REQUEST['c'])) {
		if (!empty($_REQUEST['c'])) {
			file_put_contents($file, 'code=' . trim($_REQUEST['c']) . '&is_numeric=' . trim($_REQUEST['is_numeric']));
		}
	}

	parse_str(file_get_contents($file));

	$foo->json(array(
		'code' => strtoupper(trim($code)),
		'is_numeric' => trim($is_numeric)
	));

?>
