<?php
    
    // This code is executed by Twilio.
    // URL is configured in our Twilio account per phone number. 

    require_once('../classes/SmsCallback.php');
    require_once('../classes/Config.php');

    $foo = new SmsCallback();
    $foo->received($_REQUEST);

    $message = '<Message>' . WRONG_CODE_MSG . '</Message>';
    $code = file_get_contents('../code/code.txt');

    if ($code !== false) {
    	if (!empty($code) && isset($_REQUEST['Body'])) {
    		if (strtolower(trim($code)) == strtolower(trim($_REQUEST['Body']))) {
    			// correct code, don't send a response
                $message = '';
    		}
    	}
    }

    header("content-type: text/xml");
    echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
    echo '<Response>' . $message . '</Response>';
    
?>
