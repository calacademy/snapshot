<?php

    // This code is executed by Twilio.
    // URL is configured in our Twilio account per phone number. 

    require_once('../classes/SmsCallback.php');
    require_once('../classes/Config.php');

    $foo = new SmsCallback();
    $locked = $foo->isLocked($_REQUEST['To']);
    $triggerShutter = $foo->received($_REQUEST);

    $message = $triggerShutter ? '' : '<Message>' . WRONG_CODE_MSG . '</Message>';

    if ($locked) {
        $message = '<Message>' . BUSY_MSG . '</Message>';
    }

    header("content-type: text/xml");
    echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
    echo '<Response>' . $message . '</Response>';
    
?>
