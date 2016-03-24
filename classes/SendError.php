<?php

    require 'Config.php';
    require 'Services/Twilio.php';

    class SendError {
        private $_client;

        public function __construct () {
            $this->_client = new Services_Twilio(TWILIO_SID, TWILIO_TOKEN);
        }

        public function send ($recipient) {
            try {
                $sms = $this->_client->account->messages->sendMessage(
                    TWILIO_NUMBER,
                    $recipient,
                    WRONG_CODE_MSG
                );
            } catch (Services_Twilio_RestException $e) {
                error_log($e->getMessage());
            }
        }
    }

?>
