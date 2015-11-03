<?php
    
    require_once('../classes/SmsCallback.php');

    $foo = new SmsCallback();
    $foo->received($_REQUEST);

    header("content-type: text/xml");
    echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";

?>
<Response>
    <!-- <Message>Got it!</Message> -->
</Response>
