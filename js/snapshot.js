var Snapshot = function (twilioNumber) {
	var _cam;
	var _intervalCountdown;
	var _num;
	var _count;
	var _secs;
	var _uid_sms;
	var _smssid;
	var _currentTime;
	var _code;
	var _codeBypass = 'bypass';
	var _isNumeric;

	var _words = [
		'leaf',
		'tree',
		'hoof',
		'spots',
		'herd'
	];

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
		_resetCode(_startPolling);
	}

	var _startPolling = function () {
		$('#snap-container').empty();
		$('html').removeClass('drop');

		$('#message').html('<h1>Text <strong id="txt-message">' + _code.toUpperCase() + '</strong> to<br /><strong>(415) 214-9513</strong></h1>');
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

				var body = _getSanitizedCode(data[0].body);
				var c = _code.toLowerCase();

				if (body == c || c == _codeBypass) {
					// code matches
					_resetCode();
					_startCountdown();
				} else {
					// code mismatch
					pollAgain();
				}
			} else {
				pollAgain();
			}
		});
	}

	var _getSanitizedCode = function (input) {
		input = $.trim(input).toLowerCase();

		// strip everything except digits
		if (_isNumeric) return input.replace(/\D/g, '');

		// strip everything except letters
		return input.replace(/[^a-z]/g, '');
	}

	var _onSnapshotSent = function (data, status, xhr) {
		$('html').removeClass('count-down');
		$('html').removeClass('flash');
		$('html').addClass('drop');
		$('video').get(0).play();

		setTimeout(_startPolling, 5000);
	}

	var _getCode = function () {
		if (parseInt($.getQueryString('bypass')) == 1) {
			return _codeBypass;
		}

		if (_isNumeric) {
			return Math.round(Math.random() * 999);	
		}

		return _words[Math.floor(Math.random() * _words.length)];
	}

	var _resetCode = function (callback) {
		$.getJSON('https://legacy.calacademy.org/snapshot/code/', {
			c: _getCode(),
			num: twilioNumber,
			is_numeric: _isNumeric
		}, function (data, textStatus, jqXHR) {
			_code = data.code;
			
			if (typeof(callback) == 'function') {
				callback();
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
		_isNumeric = (parseInt($.getQueryString('bypass')) != 1) && (parseInt($.getQueryString('words')) != 1);
		_setCountdownSecs();
		$(window).on('resize', _onResize);

		$('#message').html('<h1>Initializing camera&hellip;</h1>');

		_cam = new SayCheese('#stream-container', {
			camResolution: _camDimensions
		});

		_cam.on('error', _onCamError);
		_cam.on('start', _onCamStart);
		_cam.on('snapshot', _onCamSnapshot);
		_cam.start();
	}

	this.__construct();
}
