<?php

    // This code is executed by Twilio.
    // URL is configured in our Twilio account per phone number. 

    require_once('../classes/SmsCallback.php');
    require_once('../classes/Config.php');

    $foo = new SmsCallback();
    $result = $foo->received($_REQUEST);
    
    if (is_array($result)) {
        if (!$result['correct_code']) {
            // wrong code
            $message = '<Message>' . WRONG_CODE_MSG . '</Message>';
        } else {
            if ($result['locked']) {
                // locked
                $message = '<Message>' . BUSY_MSG . '</Message>';
            } else {
                // correct, don't send a response
                $message = '';
            }
        }
    } else {
        // unknown server error
        $message = '<Message>' . BUSY_MSG . '</Message>';
    }

    

    header("content-type: text/xml");
    echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
    echo '<Response>' . $message . '</Response>';
    
?>
