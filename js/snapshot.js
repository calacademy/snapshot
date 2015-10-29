var Snapshot = function () {
	var _cam;

	var _onCamError = function (e) {
		console.log(e);
	}

	var _onCamStart = function () {
		// init interaction
		$('body').on('click', function () {
			console.log('taking a snapshot...');
			_cam.takeSnapshot();
		});
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
			success: function (data, status, xhr) {
				console.log(data)
			}
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
		_cam = new SayCheese('body', {
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
