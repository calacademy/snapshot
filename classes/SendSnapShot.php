<?php

    require 'Config.php';
    require 'bitly.php';
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
                'region' => 'us-west-1',
                'credentials' => array(
                    'key' => AWS_ACCESS_KEY_ID,
                    'secret' => AWS_SECRET_ACCESS_KEY
                )
            ));
        }

        public function addOverlay ($file) {
            $overlay = imagecreatefrompng(dirname(__FILE__) . '/../images/logo.png');
            $original = imagecreatefrompng($file);

            if ($overlay && $original) {
                imagecopy($original, $overlay, (imagesx($original) - imagesx($overlay)), (imagesy($original) - imagesy($overlay)), 0, 0, imagesx($overlay), imagesy($overlay));
                imagepng($original, $file);
            } else {
                error_log('addOverlay failed');
            }
        }

        public function upload ($filename, $file) {
            $this->addOverlay($file);

            $result = $this->_aws->putObject(array(
                'Bucket' => 'snapshots.calacademy.org',
                'Key' => $filename,
                'SourceFile' => $file
            ));

            return $result['ObjectURL'];
        }

        public function getShortUrl ($longUrl) {
            $results = bitly_get('shorten', array(
                'access_token' => BITLY_OAUTH_TOKEN,
                'domain' => 'calacade.my',
                'longUrl' => $longUrl
            ));
            
            if ($results['status_code'] != 200) return false;
            return $results['data']['url'];
        }

        public function getResponseMsg () {
            $items = array(
                'Woot—science never looked so good! xo, @calacademy #CASBigBang',
                'Nice choppers! Giraffes sure are jelly. xo, @calacademy #CASBigBang',
                'Whoa—you blinded us with science. xo, @calacademy #CASBigBang',
                'We brought the science, you brought the party. Go team! xo, @calacademy #CASBigBang'
            );

            return $items[array_rand($items)];
        }

        public function getStoreMessage ($pic) {
            $msg = $this->getResponseMsg();

            if ($pic !== null) {
                $url = $this->getShortUrl(STORE_URL . urlencode($pic));
                
                if ($url !== false) {
                    $msg = 'Looking good! Purchase custom prints, apparel and more at ' . $url;
                }
            }

            return $msg;
        }

        public function send ($recipient, $pic = null, $withStoreLink) {
            $files = null;

            if ($pic !== null) {
                $files = array($pic);
            }

            $msg = $withStoreLink ? $this->getStoreMessage($pic) : $this->getResponseMsg();

            try {
                $sms = $this->_client->account->messages->sendMessage(
                    TWILIO_NUMBER,
                    $recipient,
                    $msg,
                    $files,
                    array(
                        'StatusCallback' => TWILIO_CALLBACK
                    )
                );
            } catch (Services_Twilio_RestException $e) {
                error_log($e->getMessage());
            }
        }

        public function deleteMediaForMessage ($messageId) {
            // @note
            // full res media
            // https://api.twilio.com/2010-04-01/Accounts/{TWILIO_SID}/Messages/{MessageSid}/Media/{MediaSid}
            
            $message = $this->_client->account->messages->get($messageId);

            if ($message->num_media > 0) {
                foreach ($message->media as $media) {
                    try {
                        $this->_client->account->messages->get($message->sid)->media->delete($media->sid);
                    } catch (Services_Twilio_RestException $e) {
                        error_log($e->getMessage());
                        return false;
                    }
                }
            }

            return true;    
        }

        public function listMedia () {
            foreach ($this->_client->account->messages as $message) {
                // do something
            }
        }
    }

?>
