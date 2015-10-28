<?php 
	
	header('Cache-Control: no-cache, must-revalidate');
	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
	header('Content-type: application/json');

	$arr = array(
		'error' => false,
		'url' => false
	);

	if (!isset($_REQUEST['filename']) || !isset($_FILES['snapshot'])) {
		$arr['error'] = 'bad request';
		die(json_encode($arr));
	}

	require('../classes/SendSnapShot.php');
	$foo = new SendSnapShot();
	
	$msgs = array(
		'Damn girl.',
		'You look beautiful.',
		'You are braver than you believe.',
		'You are stronger than you seem.',
		'You rock.',
		'Golly!'
	);

	$arr['url'] = $foo->upload($_REQUEST['filename'], $_FILES['snapshot']['tmp_name']);
	$foo->send('415-653-9986', $arr['url'], $msgs[array_rand($msgs)]);

	die(json_encode($arr));

?>
