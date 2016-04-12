<?php

    // This code is executed by Twilio.
    // URL is configured in our Twilio account per phone number. 

    require_once('../classes/SmsCallback.php');
    require_once('../classes/Config.php');

    $foo = new SmsCallback();
    $foo->received($_REQUEST);

    $message = '<Message>' . WRONG_CODE_MSG . '</Message>';
    
    parse_str(file_get_contents('../code/code.txt'));
    $is_numeric = ($is_numeric === 'true') ? true : false;

    if (isset($code)) {
    	if (!empty($code) && isset($_REQUEST['Body'])) {
            $body = strtolower(trim($_REQUEST['Body']));

            if ($is_numeric) {
                // strip everything except integers
                $body = preg_replace('/[^0-9]/', '', $body);
            } else {
                // strip everything except letters
                $body = preg_replace('/[^a-z]/', '', $body);
            }

    		if (strtolower(trim($code)) == $body) {
    			// correct code, don't send a response
                $message = '';
    		}
    	}
    }

    // bypass
    if (isset($code)) {
        if ($code == 'bypass') {
            $message = '';
        }
    }

    header("content-type: text/xml");
    echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
    echo '<Response>' . $message . '</Response>';
    
?>
