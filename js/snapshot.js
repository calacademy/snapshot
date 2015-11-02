var Snapshot = function () {
	var _cam;
	var _isShooting = false;
	var _intervalCountdown;
	var _num;
	var _count = 0;
	var _secs = 5;
	var _currentTime;
	
	var _onCamError = function (e) {
		console.log(e);
	}

	var _onCamStart = function () {
		_startPolling();
	}

	var _startPolling = function () {
		$('#message').html('<h1>Send a text to<br /><strong>(415) 214-9513</strong></h1>');
		_currentTime = Math.floor(Date.now() / 1000);
		_pollShutter();
	}

	var _onCount = function () {
		_count++;

		if (_count > _secs) {
			clearInterval(_intervalCountdown);
			_shoot();
		} else {
			$('#counter').html('<h1>' + _count + '</h1>');
		}
	}

	var _shoot = function () {
		$('video').get(0).pause();
		_cam.takeSnapshot();
	}

	var _startCountdown = function () {
		$('html').addClass('count-down');
		_count = 0;

		if (_intervalCountdown) {
			clearInterval(_intervalCountdown);
		}

		_intervalCountdown = setInterval(_onCount, 1000);
		_onCount();
	}

	var _pollShutter = function () {
		$.getJSON('https://legacy.calacademy.org/snapshot/shutter/', {
			now: _currentTime
		}, function (data, textStatus, jqXHR) {
			if (data.length > 0) {
				// alert(data[0].num_from + "\n" + data[0].body);
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
		$('video').get(0).play();
		$('#message').html('<h1>Check your phone</h1>');
		_startPolling();
	}

	var _onCamSnapshot = function (snapshot) {
		// send image to server for processing and transmission
		var formData = new FormData();
		formData.append('filename', 'test.png');
		formData.append('snapshot', _dataURItoBlob(snapshot.toDataURL('image/png')));

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
