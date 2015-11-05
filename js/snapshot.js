var Snapshot = function () {
	var _cam;
	var _isShooting = false;
	var _intervalCountdown;
	var _num;
	var _count;
	var _secs = 6;
	var _currentTime;
	
	var _onCamError = function (e) {
		console.log(e);
	}

	var _onCamStart = function () {
		_startPolling();
	}

	var _startPolling = function () {
		$('#snap-container').empty();
		$('html').removeClass('drop');

		$('#message').html('<h1>Send a text to<br /><strong>(415) 214-9513</strong></h1>');
		_currentTime = Math.floor(Date.now() / 1000);
		_pollShutter();
	}

	var _onCount = function () {
		_count--;

		if (_count == 0) {
			$('#counter').html('<h2>Smile!</h2>');
		} else if (_count == -1) {
			clearInterval(_intervalCountdown);
			_shoot();
		} else {
			if (_count == 1) {
				$('#message').html('<h1><strong>Check your phone!</strong></h1><h2>~30 secs selfie roundtrip</h2>');
			}
			
			$('#counter').html('<h1>' + _count + '</h1>');
		}
	}

	var _shoot = function () {
		$('video').get(0).pause();
		_cam.takeSnapshot();
	}

	var _startCountdown = function () {
		$('html').addClass('count-down');
		
		_count = _secs;

		if (_intervalCountdown) {
			clearInterval(_intervalCountdown);
		}

		_intervalCountdown = setInterval(_onCount, 1000);
		_onCount();
	}

	var _pollShutter = function () {
		$.getJSON('https://legacy.calacademy.org/snapshot/shutter/', {
			nocache: Math.random(),
			now: _currentTime
		}, function (data, textStatus, jqXHR) {
			if (data.length > 0) {
				_isShooting = true;
				_num = data[0].num_from;
				_startCountdown();
			} else {
				_pollShutter();	
			}
		});
	}

	var _onSnapshotSent = function (data, status, xhr) {
		$('html').removeClass('count-down');
		$('html').removeClass('flash');
		$('html').addClass('drop');
		$('video').get(0).play();
		
		setTimeout(_startPolling, 5000);
	}

	var _onCamSnapshot = function (snapshot) {
		// create still
		var snap = snapshot.toDataURL('image/png');
		var img = $('<img src="' + snap + '" />');
		$('#snap-container').html(img);
		$('html').addClass('flash');

		// send image to server for processing and transmission
		var formData = new FormData();
		formData.append('num', _num);
		formData.append('filename', 'test.png');
		formData.append('snapshot', _dataURItoBlob(snap));

		$.ajax({
			url: 'ajax/',
			type: 'POST',
			contentType: false,
			processData: false,
			data: formData,
			success: _onSnapshotSent
		});
	}

	var _dataURItoBlob = function (dataURI) {
		var binary = atob(dataURI.split(',')[1]);
		var array = [];

		for (var i = 0; i < binary.length; i++) {
			array.push(binary.charCodeAt(i));
		}

		var mimeString = dataURI.split(',')[0].split(':')[1].split(';')[0];

		return new Blob([new Uint8Array(array)], {
			type: mimeString
		});
	}

	this.__construct = function () {
		_cam = new SayCheese('#stream-container', {
			camResolution: {
				width: 1280,
				height: 720
			}
		});

		_cam.on('error', _onCamError);
		_cam.on('start', _onCamStart);
		_cam.on('snapshot', _onCamSnapshot);
		_cam.start();
	}

	this.__construct();	
}
