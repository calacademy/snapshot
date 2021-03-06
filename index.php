<html>
	<head>
		<title>CalAcademy</title>

		<link type="text/css" rel="stylesheet" href="//cloud.typography.com/6161652/769662/css/fonts.css" media="all" />
		<link type="text/css" rel="stylesheet" href="stylesheets/styles.css" media="all" />

		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.1/jquery.min.js"></script>
    	<script src="js/jquery.querystring.js"></script>
    	<script src="js/say-cheese.js"></script>
    	<script src="js/snapshot.js"></script>
    	
    	<script>

    		<?php require_once('classes/Config.php'); ?>

    		$(document).ready(function () {
    			var foo = new Snapshot('<?php echo TWILIO_NUMBER; ?>');
    		});
    		
    	</script>

	</head>

	<body>
		<div id="counter">
		</div>
		<div class="fill" id="snap-container">
		</div>
		<div id="message">
		</div>
		<div class="fill" id="stream-container">
		</div>
	</body>
</html>
