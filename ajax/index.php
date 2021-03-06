<?php 
	
	header('Cache-Control: no-cache, must-revalidate');
	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
	header('Content-type: application/json');

	$arr = array(
		'error' => false,
		'url' => false,
		'recipient' => false
	);

	if (!isset($_REQUEST['num']) || !isset($_REQUEST['smssid']) || !isset($_FILES['snapshot'])) {
		$arr['error'] = 'bad request';
		die(json_encode($arr));
	}

	require('../classes/SendSnapShot.php');
	$foo = new SendSnapShot();

	// upload image capture
	$arr['recipient'] = trim($_REQUEST['num']);
	$arr['url'] = $foo->upload($_REQUEST['smssid'] . '.png', $_FILES['snapshot']['tmp_name']);
	
	// send image and maybe a store link
	$store = (intval($_REQUEST['store']) == 1);
	$foo->send($arr['recipient'], $arr['url'], $store);

	die(json_encode($arr));

?>
