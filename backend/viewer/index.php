<style>

    body {
        background-color: #000;
        margin: 0;
        padding: 0;
        margin: 10px;
        font-family: sans-serif;
    }

    ul, li, img {
        margin: 0;
        padding: 0;
        max-width: 1280px;
        width: 100%;
        list-style: none;
    }

    li {
        margin-bottom: 10px;
        position: relative;
    }

    div {
        color: #fff;
        z-index: 2;
        top: 10px;
        left: 10px;
        position: absolute;
    }

</style>

<?php

    require '../../classes/Config.php';
    require '../../classes/aws/aws-autoloader.php';
    use Aws\S3\S3Client;

    class SnapS3 {
        private $_aws;

        public function __construct () {
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

        public function listFiles () {
            $iterator = $this->_aws->getIterator('ListObjects', array(
                'Bucket' => 'snapshots.calacademy.org'
            ), array(
                'limit' => 999,
                'page_size' => 100
            ));

            // order
            $ordered = array();

            foreach ($iterator as $object) {
                $time = strtotime($object['LastModified']);
                $ordered[$time] = $object;
            }

            krsort($ordered);

            // render
            echo '<ul>';

            foreach ($ordered as $key => $object) {
                echo '<li>';
                echo '<img src="https://s3-us-west-1.amazonaws.com/snapshots.calacademy.org/' . $object['Key'] . '" />';
                echo '<div>' . date('m/d/y g:ia', $key) . '</div>';
                echo '</li>';
            }

            echo '</ul>';
        }
    }

    if ($_POST['aws'] == AWS_ACCESS_KEY_ID && $_SERVER['HTTPS'] == 'on') {
        $foo = new SnapS3();
        $foo->listFiles();
    } else if ($_SERVER['HTTPS'] == 'on') {
        echo '<form action="." method="post"><input placeholder="AWS Access Key" type="password" id="aws" name="aws" /><input type="submit" value="Submit" /></form>';
    } else {
        echo '<div>boohoo!</div>';
    }

?>
