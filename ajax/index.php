<?php 
	
	header('Cache-Control: no-cache, must-revalidate');
	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
	header('Content-type: application/json');

	$arr = array(
		'error' => false,
		'url' => false,
		'recipient' => false
	);

	if (!isset($_REQUEST['num']) || !isset($_REQUEST['filename']) || !isset($_FILES['snapshot'])) {
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

	$arr['recipient'] = trim($_REQUEST['num']);
	$arr['url'] = $foo->upload($_REQUEST['filename'], $_FILES['snapshot']['tmp_name']);
	$foo->send($arr['recipient'], $arr['url'], $msgs[array_rand($msgs)]);

	die(json_encode($arr));

?>
