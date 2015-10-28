<?php
    
    require 'Config.php';
    require 'Services/Twilio.php';
    require 'aws/aws-autoloader.php';
    use Aws\S3\S3Client;

    class SendSnapShot {
        private $_client;
        private $_aws;

        public function __construct () {
            $this->_client = new Services_Twilio(TWILIO_SID, TWILIO_TOKEN);

            $this->_aws = S3Client::factory(array(
                'profile' => 'default',
                'version' => '2006-03-01',
                'region' => 'us-west-1'
            ));    
        }

        public function upload ($filename, $file) {
            $result = $this->_aws->putObject(array(
                'Bucket' => 'snapshots.calacademy.org',
                'Key'    => $filename,
                'SourceFile'   => $file
            ));

            return $result['ObjectURL'];
        }

        public function send ($recipient, $pic = null, $msg = 'You look amazing.') {
            if ($pic === null) {
                $sms = $this->_client->account->messages->sendMessage(
                    TWILIO_NUMBER, 
                    $recipient,
                    $msg
                );
            } else {
                $sms = $this->_client->account->messages->sendMessage(
                    TWILIO_NUMBER, 
                    $recipient,
                    $msg,
                    array($pic)
                );
            }
        }
    }

?>