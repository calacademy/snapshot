var Snapshot = function () {
	var _cam;
	var _intervalCountdown;
	var _num;
	var _count;
	var _secs;
	var _uid_sms;
	var _smssid;
	var _currentTime;
	var _code;

	var _camDimensions = {
		width: 1280,
		height: 720
	};

	var _onResize = function () {
		_fill($('#snap-container img'));
		_fill($('#stream-container video'));
	}

	var _fill = function (el) {
		if (el.length == 0) return;

		var ratio = _camDimensions.width / _camDimensions.height;
		var containerRatio = $(window).width() / $(window).height();

		var css = {
          position: 'relative',
          left: 0,
          top: 0
        };

		if (ratio > containerRatio) {
			css.width = Math.ceil($(window).height() * ratio);
			css.height = $(window).height();
			css.left = Math.round(($(window).width() - css.width) / 2) + 'px';
		} else {
			css.width = $(window).width();
			css.height = Math.ceil($(window).width() / ratio);
			css.top = Math.round(($(window).height() - css.height) / 2) + 'px';
		}

		el.css(css);
	}

	var _onCamError = function (e) {
		console.log(e);
	}

	var _onCamStart = function () {
		_onResize();
		_resetCode();
	}

	var _startPolling = function () {
		$('#snap-container').empty();
		$('html').removeClass('drop');

		$('#message').html('<h1>Text <strong id="txt-message">SELFIE</strong> to<br /><strong>(415) 214-9513</strong></h1>');
		_currentTime = Math.floor(Date.now() / 1000);
		_pollShutter();
	}

	var _onCount = function () {
		_count--;

		if (_count == 0) {
			$('#counter').html('<h2 id="smile">Smile!</h2>');
		} else if (_count == -1) {
			clearInterval(_intervalCountdown);
			_shoot();
		} else {
			if (_count == 1) {
				$('#message').html('<h1><strong>Check your phone!</strong></h1><h2>~30 secs selfie roundtrip</h2>');
			}

			$('#counter').html('<h2 id="directions">Stand on the <strong>X</strong></h2><h1>' + _count + '</h1>');
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
		console.log(_currentTime);

		var pollAgain = function () {
			setTimeout(_pollShutter, 1000);
		}

		$.getJSON('https://legacy.calacademy.org/snapshot/shutter/', {
			nocache: Math.random(),
			now: _currentTime
		}, function (data, textStatus, jqXHR) {
			// nothing
			if (data.length == 0) {
				pollAgain();
				return;
			}

			var uid_sms = parseInt(data[0].uid_sms)

			// setup
			if (!_uid_sms) {
				_uid_sms = uid_sms;
				pollAgain();
				return;
			}

			// check if there's a newer request
			if (uid_sms > _uid_sms) {
				_uid_sms = uid_sms;
				_smssid = data[0].smssid;
				_num = data[0].num_from;

				var body = $.trim(data[0].body).toLowerCase();

				if (body == _code.toLowerCase()) {
					// code matches, start countdown
					_startCountdown();
				} else {
					// code mismatch, send error txt
					_sendErrorMsg(_num);
					pollAgain();
				}
			} else {
				pollAgain();
			}
		});
	}

	var _sendErrorMsg = function (myNum) {
		console.log('attempting to send error msg to ' + myNum);

		$.ajax({
			url: 'error/',
			type: 'POST',
			data: {
				num: myNum
			},
			success: function (data, textStatus, jqXHR) {
				if (data.success) {
					console.log('error msg sent to ' + data.recipient);
				}
			}
		});
	}

	var _onSnapshotSent = function (data, status, xhr) {
		$('html').removeClass('count-down');
		$('html').removeClass('flash');
		$('html').addClass('drop');
		$('video').get(0).play();

		setTimeout(_resetCode, 5000);
	}

	var _resetCode = function () {
		$.ajax({
			url: 'code/',
			type: 'POST',
			data: {
				generate: 1
			},
			success: function (data, textStatus, jqXHR) {
				_code = data.code;
				_startPolling();
			}
		});
	}

	var _onCamSnapshot = function (snapshot) {
		// create still
		var snap = snapshot.toDataURL('image/png');
		var img = $('<img src="' + snap + '" />');
		$('#snap-container').html(img);
		$('html').addClass('flash');

		_onResize();

		// send image to server for processing and transmission
		var formData = new FormData();
		formData.append('num', _num);
		formData.append('smssid', _smssid);
		formData.append('snapshot', _dataURItoBlob(snap));

		var store = (parseInt($.getQueryString('store')) == 1) ? '1' : '0';
		formData.append('store', store);

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

		var i = 0;

		while (i < binary.length) {
			array.push(binary.charCodeAt(i));
			i++;
		}

		var mimeString = dataURI.split(',')[0].split(':')[1].split(';')[0];

		return new Blob([new Uint8Array(array)], {
			type: mimeString
		});
	}

	var _onStart = function (e) {
		$('body').off('click');
		$('#message').html('<h1>Initializing camera&hellip;</h1>');
		$(document).fullScreen(true);

		_cam = new SayCheese('#stream-container', {
			camResolution: _camDimensions
		});

		_cam.on('error', _onCamError);
		_cam.on('start', _onCamStart);
		_cam.on('snapshot', _onCamSnapshot);
		_cam.start();

		return false;
	}

	var _setCountdownSecs = function () {
		var secs = parseInt($.getQueryString('secs'));

		if (isNaN(secs) || secs < 3) {
			// default
			secs = 10;
		}

		// add a second of padding
		secs++;
		_secs = secs;
	}

	this.__construct = function () {
		_setCountdownSecs();
		$(window).on('resize', _onResize);

		$('body').on('click', _onStart);
		$('#message').html('<h1>Click to begin</h1>');
	}

	this.__construct();
}
