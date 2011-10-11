if (typeof(AC) == "undefined") { AC = {}; }

AC.Quicktime = {
	
	/**
	* Collection of all controllers that have a movie attached
	**/
	controllers: [],
	
	/**
	 * Adds an object param tag to the specified parent
	 * Note that the attributes are added in this seemingly odd order
	 * so they show up in the logical order in the dom
	 */
	_addParameter: function(parent, name, value) {

		if (!parent) {
			return;
		}

		var param = document.createElement('param');
		param.setAttribute('value', value);
		param.setAttribute('name', name);
		parent.appendChild(param);
		
		param = null;

	},
	
	/**
	 * Creates the IE friendly outer object
	 * NOTE Safari and Opera both seem to be able to use this one as well
	 * I'm assuming this is due to some hacking on their part for
	 * compatibility
	 */
	_createOuterObject: function(name, fileUrl, options) {

		var outerObject = document.createElement('object');
		
		if(AC.Detector.isMobile() && options.posterFrame) {
			AC.Quicktime._addParameter(outerObject, 'src', options.posterFrame);
			AC.Quicktime._addParameter(outerObject, 'href', fileUrl);
			AC.Quicktime._addParameter(outerObject, 'target', 'myself');
		} else {
			AC.Quicktime._addParameter(outerObject, 'src', fileUrl);
		}
		
		outerObject.setAttribute('id', name);


		var activexVersion = '7,3,0,0';

		if (null != options && '' != options.codebase && typeof(options.codebase) != 'undefined') {
			activexVersion = options.codeBase;
		}

		outerObject.setAttribute('codebase', 
		    'http://www.apple.com/qtactivex/qtplugin.cab#version=' + activexVersion);

		return outerObject;
	},
	
	/**
	 * Creates the more standards compliant object which Firefox and Netscape
	 * rely on to load the movie
	 */
	_createInnerObject: function(name, fileUrl, options) {

		var innerObject = document.createElement('object');
        
		innerObject.setAttribute('type', 'video/quicktime');
		innerObject.setAttribute('data', fileUrl);
		innerObject.setAttribute('id', name + "Inner");

		return innerObject;
	},
	
	/**
	* Creates a movie just to trigger the native activeX/missing plugin dialog in a browser
	*/
	_createNullMovie: function(width, height) {
		width = 0;
		height = 0;
		var nullContainer = $(document.createElement('div'));
		
		//needed in the object to trigger the missing activeX control in IE
		var classid = "clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B";
		var codebase = 'http://www.apple.com/qtactivex/qtplugin.cab#version=7,3,0,0';
		
		// triggers the missing plugin for other browsers like firefox
		var pluginspage = 'http://www.apple.com/quicktime/download/';

		nullContainer.innerHTML = '<object width="' + width + '" height="' + height + '" classid="' + classid +'" codebase="' + codebase + '"><embed width="' + width + '" height="' + height + '" type="video/quicktime" pluginspage="' + pluginspage + '"></embed></object>'
		
		return nullContainer;
	},
	
	/**
	 * Attaches supplied options as necessary to the movie objects
	 */
	_configureMovieOptions: function(innerObject, outerObject, options) {
		
		if(null == options || typeof(options) == 'undefined') {
			return false;
		}
			
		for (var property in options) {

			var attributeName = property.toLowerCase();

			switch(attributeName) {
				case('type'):
				case('src'):
				case('data'):
				case('classid'):
				case('name'):
				case('id'):
					//do nothing as these shouldn't be overriden
				break;
				case('class'):
					Element.addClassName(outerObject, options[property]);
				break;
				case('innerId'):
					if(innerObject) {
						innerObject.setAttribute('id', options[property]);
					}
				break;
				case('width'):
				case('height'):
					outerObject.setAttribute(attributeName, options[property]);
					if(innerObject) {
						innerObject.setAttribute(attributeName, options[property]);
					}
				break;
				default:
					AC.Quicktime._addParameter(outerObject, attributeName, options[property]);
					AC.Quicktime._addParameter(innerObject, attributeName, options[property]);
				break;
			}

		}
		
	},
	
	/**
	 * Will create an object element to append to the document with the
	 * specified parameters as well as any additional options
	 * 
	 * TODO plenty of work to do with the optional parameters I'm sure
	 * Though I don't know what custom parameters we may need to faciliate
	 * whatever it is we need to do with movies, though tehcnially you can
	 * pass whatever you wantt in here and it'll handle it pretty well
	 * 
	 * It's often just a matter of if we expose a custom parameter we'll
	 * accept as part of the options hash...where/how does that get applied?
	 */
	packageMovie: function(name, fileUrl, options) {
		
		if(name == null || fileUrl == null ) {
			throw new TypeError('Valid Name and File URL are required arguments.');
		}
		
		var minVersion = '7';
		if (options && options.minVersion) {
			minVersion = options.minVersion;
		}
		
		// If required QuickTime is not available provide a link to download instead of a movie object
		if (!AC.Detector.isMobile() && !AC.Detector.isValidQTAvailable(minVersion)) {
			var downloadNotice = $(document.createElement('a'));
			downloadNotice.addClassName('quicktime-download');
			
			downloadNotice.setAttribute('href', 'http://www.apple.com/quicktime/download/');
			downloadNotice.innerHTML = options.downloadText || 'Get the Latest QuickTime.';
			
			// downloadNotice.appendChild(this._createNullMovie(options.width, options.height));
			return downloadNotice;
		}
		
		var outerObject = AC.Quicktime._createOuterObject(name, fileUrl, options);
		
		if (!AC.Detector.isIEStrict()) {
			//really imperitive we don't create an inner object for IE
			//this is what causes the N items remaining as it never actually 
			//gets requested bbut IE still decides to report it as 
			//needing to be loaded
			
			var innerObject = AC.Quicktime._createInnerObject(name, fileUrl, options);
			outerObject.appendChild(innerObject);
			
		} else if(options.aggressiveCleanup !== false){
			
			//knowing it's IE at this point, make sure we clear the movie when the page closes
			//we also set our reference to null for good measure
			Event.observe(window, 'unload', function() {
				try {
					outerObject.Stop();
				} catch(e) {;}
				outerObject.style.display = 'none';
				outerObject = null;
				
				//this only masks memory leaks
				//movie could still be present even if hidden
				//if the movie remains in the viewport upon visiting other
				//pages, you have remaining references to the movie somewhere
				//they need to be removed as early as possible preferably
				//or at least before you leave if you have no other choice
				
				//closing the window that is leaking will throw an error message
				//but at least due to the hiding it won't completely interrupt
				//browsing
			});
			
		}

		AC.Quicktime._configureMovieOptions(innerObject, outerObject, options);
		
		//force preservation of existing params even if the movie's URL is changed
		//this is the ooopiste of how QT works by default
		
		AC.Quicktime._addParameter(outerObject, 'saveembedtags', true);
		AC.Quicktime._addParameter(innerObject, 'saveembedtags', true);
		
		//Needs to be last so IE sees all the parameters appended to
		//the object prior to loading the activex control
		outerObject.setAttribute('classid', 
			'clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B');
		
		return outerObject;
	},

	/**
	 * Using this will clear the contents of oldMovie's parentNode and 
	 * insert newMovie into the container
	 */
	swapMovie: function(container, oldMovie, newMovie) {
		
		
		var isIE = AC.Detector.isIEStrict();

		//in IE ensure we hide the old movie, otherwise it kinda ghosts itself
		if(isIE && oldMovie) {
			oldMovie.style.display = 'none';
		}
		
		//Yes, we could preserve the nodes in this container in some browsers
		//but since IE is overwriting the innerHTML we're going to completely
		//empty the container everywhere for the sake of consistency
		Element.removeAllChildNodes(container);
		
		var movieReference = null; 

		if (!isIE) {
			container.appendChild(newMovie);
			movieReference = newMovie;
		} else {
			container.innerHTML = newMovie.toMarkup();
			movieReference = container.firstChild;
			movieReference.style.display = 'block';
		}
		
		//reference to the movie that's actually in the DOM
		return movieReference;
	},
	
	/**
	* Helper to handle a very simple insertion of a movie into a specified 
	* element
	*/
	render: function(movie, element, options) {

		var target = $(element);
		var placeholderContent = target.innerHTML;
		
		if (!AC.Detector.isQTInstalled()) {
			Element.addClassName(target, 'static');
			return
		}
		
		target.innerHTML = '';
		Element.addClassName(target, 'loading');
		target.appendChild(movie);
		
		var options = options ? options : {};
			
		var movieLoaded = function() {
			checkController.monitorMovie();
			Element.removeClassName(element, "loading");
			Element.addClassName(element, "loaded");
		}
		
		var movieFinished = function() {return;}
		
		if("finishedState" in options) {
			
			var showFinishedState = function(target, content) {
				return (function() {
					
					checkController.detachFromMovie();
					
					//ensure movie is hidden in IE
					movie.style.display = 'none';
					movie = null;
					
					target.innerHTML = '';
					
					if (typeof(content) == 'string') {
						target.innerHTML = content;
					} else {
						target.appendChild(content);
					}
				})
			}
			
			movieFinished = showFinishedState(target, options.finishedState)
		}
		
		var checkController = new AC.QuicktimeController(movie, {
			onMoviePlayable: movieLoaded, 
			onMovieFinished: movieFinished})
	}

};

/**
 * If you run into issues trying to control a movie:
 *  - Movie needs to be at least 1x1 px to be addressable in firefox
 *  - Movie needs to be visible on screen to be addressable in firefox
 *  - Removing the movie object from the DOM and then putting it back 
 *    will often break the connection a controller may have had. Need to 
 *    test to see if you can simply reattach or if you need a new movie object.
 */
AC.QuicktimeController = Class.create();
AC.QuicktimeController.prototype = {
	
	movie: null,
	options: null,
		
	movieAttacher: null,
	attachDelay: 10,
	
	movieWatcher: null,
	monitorDelay: 400,
	currentTime: 0,
	percentLoaded: 0,
	maxBytesLoaded: 0,
	movieSize: 0,
	
	allowAttach: true,
	
	controllerPanel: null,
	currentControl: null,
	playControl: null,
	pauseControl: null,
	slider: null,
	track: null,
	playHead: null,
	loadedProgress: null,
	
	isJogging: false,
	hardPaused: false,
	duration: 0,
	finished: false,
	playing: false,
	
	unloader: null,
	
	/**
	 * initialize the controller with optional movie object and parameters
	 */
	initialize: function(movie, options) {
		if(typeof(movie) != 'undefined') {
			this.attachToMovie(movie, options);
		}
	},
	
	/**
	 * attaches the controller to a specified movie so we can control it
	 */
	attachToMovie: function(movie, options) {
		
		if(!this.allowAttach) {
			return;
		}
		
		clearInterval(this.movieAttacher);
		
		this.options = options || {};
		
		if (null == $(movie)) {
			throw 'Movie has to be appended to document prior to attaching to with a controller.';
		}
		
		//if not scheduled for detaching on unload, schedule detachment
		if (!this.unloader) {
			this.unloader = this.detachFromMovie.bind(this);
			Event.observe(window, 'unload', this.unloader);
		}
		
		this.movieAttacher = setInterval(this._attach.bind(this, movie), this.attachDelay);
		
		movie = null;
		
	},
	
	/**
	 * Actually tries to attach to the correct movie object
	 */
	_attach: function(movie) {
		
		if(!this.allowAttach) {
			return;
		}
		
		var status = null;

		try {
			this.movie = movie;
			status = this.movie.GetPluginStatus();
		} catch(e) {
			try {
				this.movie = movie.getElementsByTagName('object')[0];
				status = this.movie.GetPluginStatus();
			} catch(e) {
				this.movie = null;
			}
		}
		
		movie = null;
		
		if (status == 'Playable' || status == 'Complete') {
			clearInterval(this.movieAttacher);

			//TODO expose this in property listing above if we keep it
			if (!this.alreadyRecorded) {
				// AC.Quicktime.controllers.push(this);
				this.alreadyRecorded = true;
			}
			
			if (typeof(this.options.onMoviePlayable) == 'function') { 
				this.options.onMoviePlayable();
			}
			
		} else if (status && status.match(/error/i)) {
			clearInterval(this.movieAttacher);
		}
	},
	
	/**
	 * stops monitoring the current movie and forgets the current movie
	 * also needs to consider that the movie is in the process of attaching 
	 * still
	 */
	detachFromMovie: function() {
		this.movie = null;
		this.allowAttach = false;
		
		clearInterval(this.movieAttacher);
		clearTimeout(this.movieWatcher);
		
		AC.Quicktime.controllers = AC.Quicktime.controllers.without(this);
		
		this.reset();
		
		//if we've manually detached, there's no need to do it on unload
		Event.stopObserving(window, 'unload', this.unloader);
		this.unloader = null;
		
		this.allowAttach = true;
	},
	
	/* Reset cached values but don't detach from the current movie */
	reset: function() {
		this.duration = 0;
		this.movieSize = 0;
		this.percentLoaded = 0;
		
		if(this.slider) {
			this.slider.setValue(0);
			this.slider.trackLength = this.slider.maximumOffset() - this.slider.minimumOffset();
		}
	},
	
	/**
	 * renders the human friendly GUI custom controller and starts monitoring 
	 * the movie
	 */
	render: function(containerId) {
		
		this.controllerPanel = document.createElement('div');
		Element.addClassName(this.controllerPanel, 'ACQuicktimeController');

		//TODO encapsulate button creation
		this.playControl = document.createElement('div');
		Element.addClassName(this.playControl, 'control');
		Element.addClassName(this.playControl, 'play');
		this.playControl.innerHTML = 'Play';
		this.playControl.onclick = this.Play.bind(this);

		this.pauseControl = document.createElement('div');
		Element.addClassName(this.pauseControl, 'control');
		Element.addClassName(this.pauseControl, 'pause');
		this.pauseControl.innerHTML = 'Pause';
		this.pauseControl.onclick = this.Stop.bind(this);
		
		var playing = false;
		
		if(null != this.movie) {
			playing = this.GetAutoPlay();
		}
		
		this.currentControl = (playing) ? this.pauseControl : this.playControl;
		this.controllerPanel.appendChild(this.currentControl);
		
		this.sliderPanel = document.createElement('div');
		Element.addClassName(this.sliderPanel, 'sliderPanel');
		
		this.track = document.createElement('div');
		Element.addClassName(this.track, 'track');
		this.sliderPanel.appendChild(this.track);
		
		this.loadedProgress = document.createElement('div');
		Element.addClassName(this.loadedProgress, 'loadedProgress');
		this.track.appendChild(this.loadedProgress);
		
		this.trackProgress = document.createElement('div');
		Element.addClassName(this.trackProgress, 'trackProgress');
		this.track.appendChild(this.trackProgress);
		
		this.playHead = document.createElement('div');
		Element.addClassName(this.playHead, 'playHead');
		this.track.appendChild(this.playHead);
		
		this.controllerPanel.appendChild(this.sliderPanel);
		

		if (containerId) {
			
			$(containerId).appendChild(this.controllerPanel);
			
			if(this.movie) { 
				this.monitorMovie();
			}
		}

		return this.controllerPanel;
	},
	
	//Actually monitors the movie, this is the performance intensive inner loop that checks the movie periodically
	_monitor: function() {
		
		if (null === this.movie) {
			return;
		}

		var isPlaying = this.isPlaying();
		
		if(this.controllerPanel !== null) {

			var shouldBePauseable = isPlaying && (this.currentControl == this.playControl);
			var shouldBePlayable = !isPlaying && (this.currentControl == this.pauseControl);
	
			if (!this.isJogging && shouldBePauseable) {
				this.controllerPanel.replaceChild(this.pauseControl, this.currentControl);
				this.currentControl = this.pauseControl;
			} else if (!this.isJogging && shouldBePlayable) {
				this.controllerPanel.replaceChild(this.playControl, this.currentControl);
				this.currentControl = this.playControl;
			}
	
			//update the loaded indicator
			if(this.percentLoaded < 1) {
			
				var trackWidth = Element.getDimensions(this.track).width;
			
				var loaded = this.GetMaxBytesLoaded()/this.GetMovieSize();
			
				if(!isNaN(loaded) && 0 !== loaded) {
					this.percentLoaded = loaded;
				}
				
				var progressWidth = trackWidth * this.percentLoaded;
				Element.setStyle(this.loadedProgress, {width: progressWidth + 'px'});
			}

			//unless the user is jogging the controller, update to reflect movie's status
			if(isPlaying) {
				this.slider.setValue(this.GetTime()/this.GetDuration());
			}
		
		}
		
		//is the movie playing?
		if(isPlaying) {
			
			if(!this.playing) {
				
				this.playing = true;
				
				if(typeof(this.options.onMovieStart) == 'function') {
					this.options.onMovieStart();
				}
			}
			
		} else {
			
			if(this.playing) {
				
				this.playing = false;
			
				if(typeof(this.options.onMovieStop) == 'function') {
					this.options.onMovieStop();
				}
			}
		}
		
		//has the movie just finished?
		if(this.isFinished()) {
			if(!this.finished) {
				
				//make the callback if necessary
				if(typeof(this.options.onMovieFinished) == 'function') {
					this.options.onMovieFinished();
				}
				
				this.finished = true;
			}
		} else {
			this.finished = false;
		}
		
		if(this.movie !== null) {
			this.movieWatcher = setTimeout(this._monitor.bind(this), this.monitorDelay);
		}
	},
	
	/**
	 * monitors the movie so it can update the custom controller
	 */
	monitorMovie: function() {
		
		if(this.controllerPanel !== null) {
			
			this.slider = new Control.Slider(this.playHead, this.track, {
				onSlide: function(value) {
					
					if(isNaN(value)) {
						return;
					}
					
					this.trackProgress.style.width = this.slider.translateToPx(value);
				
					//temporarily pause the movie directly to avoid hard pausing the movie
					try {
						this.movie.Stop();
				
						this.isJogging = true;
						this.SetTime(value * this.GetDuration());
						
					} catch(e) {
						//plugin failure, ignore
					}
				
				}.bind(this),
			
				onChange: function(value) {
					
					if(isNaN(value)) {
						return;
					}
					
					//Catch the case where the trackLength is being misreported as 0 
					//because it was initialized before we could get accurate offsets/styles
					if (0 == this.slider.trackLength) {
						this.slider.trackLength = this.slider.maximumOffset() - this.slider.minimumOffset();
					}
					
					this.trackProgress.style.width = this.slider.translateToPx(value);
				
					if(!this.isPlaying() && !this.hardPaused && !this.isFinished()) {
						try {
							this.movie.Play();
						} catch(e) {
							//plugin failure, ignore
						}
					}
				
					this.isJogging = false;
			
				}.bind(this)
			});
			
			//This was showing up a lot in profiling and was pretty much constantly setting and removing
			//a selected class on the slider handle. Which I couldn't care less about
			//There's probably a better way to handle this but this at least stops a lot of "unnecessary" dom class changes
			this.slider.updateStyles = Prototype.emptyFunction;
		
		}
		
		this.movieWatcher = setTimeout(this._monitor.bind(this), this.monitorDelay);
		
	},
	
	Play: function() {
		if (null != this.movie) {
			try {
				this.movie.Play();
				this.hardPaused = false;
			} catch(e) {}
		}
	},
	
	Stop: function() {
		if (null != this.movie) {
			try {
				this.movie.Stop();
				this.hardPaused = true;
			} catch(e) {}
		}
	},
	
	Rewind: function() {
		if (null != this.movie) {
			this.movie.Stop();
			this.movie.Rewind();
		}
	},
	
	Step: function(count) {
		this.movie.Step(count);
	},
	
	ShowDefaultView: function() {
		this.movie.ShowDefaultView();
	},
	
	GoPreviousNode: function() {
		this.movie.GoPreviousNode();
	},
	
	GetQuicktimeVersion: function() {
		return this.movie.GetQuickTimeVersion();
	},
	
	GetQuicktimeLanguage: function() {
		return this.movie.GetQuicktimeLanguage();
	},
	
	GetQuicktimeConnectionSpeed: function() {
		return this.movie.GetQuicktimeConnectionSpeed();
	},
	
	GetIsQuickTimeRegistered: function() {
		return this.movie.GetIsQuickTimeRegistered();
	},
	
	GetComponentVersion: function() {
		return this.movie.GetComponentVersion();
	},
	
	GetPluginVersion: function() {
		return this.movie.GetPluginVersion();
	},
	
	ResetPropertiesOnReload: function() {
		this.movie.ResetPropertiesOnReload();
	},
	
	GetPluginStatus: function() {
		return this.movie.GetPluginStatus();
	},
		
	GetAutoPlay: function() {
		return this.movie.GetAutoPlay();
	},
	
	SetAutoPlay: function(autoPlay) {
		this.movie.SetAutoPlay(autoPlay);
	},
	
	GetControllerVisible: function() {
		return this.movie.GetControllerVisible();
	},
	
	SetControllerVisible: function(visible) {
		this.movie.SetControllerVisible(visible);
	},
	
	GetRate: function() {
		return this.movie.GetRate();
	},
	
	SetRate: function(rate) {
		this.movie.SetRate();
	},
	
	GetTime: function() {
		
		var actualTime = 0;
		try {
			//IE sometimes throws an error on accessing this property on first load
			actualTime = this.movie.GetTime();
			}
		catch (e) {
			//ignore error
		}
		
		if(0 === actualTime) {
			//if we can't talk ot the plugin directly, estimate what time should be
			actualTime = this.currentTime + this.monitorDelay;
		} else {
			this.currentTime = actualTime;
		}
		
		return actualTime;
	},
	
	SetTime: function(time) {
		this.movie.SetTime(time);
	},
	
	GetVolume: function() {
		return this.movie.GetVolume();
	},
	
	SetVolume: function(volume) {
		this.movie.SetVolume(volume);
	},
	
	GetMute: function() {
		return this.movie.GetMute();
	},
	
	SetMute: function(mute) {
		this.movie.SetMute();
	},
	
	GetMovieName: function() {
		return this.movie.GetMovieName();
	},
	
	SetMovieName: function(movieName) {
		this.movie.SetMovieName(movieName);
	},
	
	GetMovieID: function() {
		return this.movie.GetMovieID();
	},
	
	SetMovieID: function(movieID) {
		this.movie.SetMovieID(movieID);
	},
	
	GetStartTime: function() {
		return this.movie.GetStartTime();
	},
	
	SetStartTime: function(time) {
		this.movie.SetStartTime(time);
	},
	
	GetEndTime: function() {
		return this.movie.GetEndTime();
	},
	
	SetEndTime: function(time) {
		this.movie.SetEndTime(time);
	},
	
	GetBgColor: function() {
		return this.movie.GetBgColor();
	},
	
	SetBgColor: function(color) {
		this.movie.SetBgColor(color);
	},
	
	GetIsLooping: function() {
		return this.movie.GetIsLooping();
	},
	
	SetIsLooping: function(loop) {
		this.movie.SetIsLooping(loop);
	},
	
	GetLoopIsPalindrome: function() {
		return this.movie.GetLoopIsPalindrome();
	},
	
	SetLoopIsPalindrome: function(loop) {
		this.movie.SetLoopIsPalindrome(loop);
	},
	
	GetPlayEveryFrame: function() {
		return this.movie.GetPlayEveryFrame();
	},
	
	SetPlayEveryFrame: function(playAll) {
		this.movie.SetPlayEveryFrame(playAll);
	},
	
	GetHREF: function() {
		return this.movie.GetHREF();
	},
	
	SetHREF: function(url) {
		this.movie.SetHREF(url);
	},
	
	GetTarget: function() {
		return this.movie.GetTarget();
	},
	
	SetTarget: function(target) {
		this.movie.SetTarget(target);
	},
	
	GetQTNEXTUrl: function() {
		return this.movie.GetQTNEXTUrl();
	},
	
	SetQTNEXTUrl: function(index, url) {
		this.movie.SetQTNEXTUrl(index, url);
	},
	
	GetURL: function() {
		return this.movie.GetURL();
	},
	
	SetURL: function(url) {
		this.movie.SetURL(url);
		//since the movie's changed make sure the controller is reset
		this.reset();
	},
	
	GetKioskMode: function() {
		return this.movie.GetKioskMode();
	},
	
	SetKioskMode: function(kioskMode) {
		this.movie.SetKioskMode(kioskMode);
	},
	
	GetDuration: function() {
		if(null == this.duration || 0 === this.duration) {
			try {
				this.duration = this.movie.GetDuration();
			} catch(e) {
				this.duration = null;
			}
		}
		return this.duration;
	},
	
	GetMaxTimeLoaded: function() {
		return this.movie.GetMaxTimeLoaded();
	},
	
	GetTimeScale: function() {
		return this.movie.GetTimeScale();
	},
	
	GetMovieSize: function() {
		if( 0 === this.movieSize) {
			try {
				this.movieSize = this.movie.GetMovieSize();
			} catch(e) {
				this.movieSize = 0;
			}
		}
		return this.movieSize;
	},
	
	GetMaxBytesLoaded: function() {
		try {
			this.maxBytesLoaded = this.movie.GetMaxBytesLoaded();
		} catch(e) {}
		
		return this.maxBytesLoaded;
	},
	
	GetTrackCount: function() {
		return this.movie.GetTrackCount();
	},
	
	GetMatrix: function() {
		return this.movie.GetMatrix();
	},
	
	SetMatrix: function(matrix) {
		this.movie.SetMatrix(matrix);
	},
	
	GetRectangle: function() {
		return this.movie.GetRectangle();
	},
	
	SetRectangle: function(rect) {
		this.movie.SetRectangle(rect);
	},
	
	GetLanguage: function() {
		return this.movie.GetLanguage();
	},
	
	SetLanguage: function(language) {
		this.movie.SetLanguage(language);
	},
	
	GetMIMEType: function() {
		return this.movie.GetMIMEType();
	},
	
	GetUserData: function(type) {
		return this.movie.GetUserData(type);
	},
	
	GetIsVRMovie: function() {
		return this.movie.GetIsVRMovie();
	},
	
	GetHotspotUrl: function(hotspotID) {
		return this.movie.GetHotspotUrl(hotspotID);
	},
	
	SetHotspotUrl: function(hotspotID, url) {
		this.movie.SetHotspotUrl(hotspotID, url);
	},
	
	GetHotspotTarget: function(hotspotID) {
		return this.movie.GetHotspotTarget(hotspotID);
	},
	
	SetHotspotTarget: function(hotspotID, target) {
		this.movie.SetHotspotTarget(hotspotID, target);
	},
	
	GetPanAngle: function() {
		return this.movie.GetPanAngle();
	},
	
	SetPanAngle: function(angle) {
		this.movie.SetPanAngle(angle);
	},
	
	GetTiltAngle: function() {
		return this.movie.GetTiltAngle();
	},
	
	SetTiltAngle: function(angle) {
		this.movie.SetTiltAngle(angle);
	},
	
	GetFieldOfView: function() {
		return this.movie.GetFieldOfView();
	},
	
	SetFieldOfView: function(fov) {
		this.movie.SetFieldOfView(fov);
	},
	
	GetNodeCount: function() {
		return this.movie.GetNodeCount();
	},
	
	SetNodeID: function(id) {
		this.movie.SetNodeID(id);
	},
	
	GetTrackName: function(index) {
		return this.movie.GetTrackName(index);
	},
	
	GetTrackType: function(index) {
		return this.movie.GetTrackType(index);
	},
	
	GetTrackEnabled: function(index) {
		return this.movie.GetTrackEnabled(index);
	},
	
	SetTrackEnabled: function(index, enabled) {
		this.movie.SetTrackEnabled(index, enabled);
	},
	
	GetSpriteTrackVariable: function(trackIndex, variableIndex) {
		return this.movie.GetSpriteTrackVariable(trackIndex, variableIndex);
	},
	
	SetSpriteTrackVariable: function(variableIndex, value) {
		this.movie.SetSpriteTrackVariable(variableIndex, value);
	},
	
	GetChapterCount: function() {
		try {
			return this.movie.GetChapterCount();
		} catch(e) {
			return NaN;
		}
	},
	
	GetChapterName: function(index) {
		try {
			return this.movie.GetChapterName(index);
		} catch(e) {
			return null;
		}
	},
	
	GoToChapter: function(name) {
		try {
			this.movie.GoToChapter(name);
			return true;
		} catch(e) {
			return false;
		}
	},
	
	
	isPlaying: function() {
		try {
			return this.movie.GetRate() !== 0
		} catch(e) {
			return false
		}
	},
	
	isFinished: function() {
		
		try {
			var isStopped = this.movie.GetRate() === 0;
			var isAtEnd = this.movie.GetTime() == this.GetDuration();
			return isStopped && isAtEnd;
		} catch (e) {
			
			/*	No firm connection with plugin
				Not exposing this exception the client as it's very common and
				somewhat unreasonable to think anybody will want to handle it
				at a higher level with anything other than assuming the movie
				is not finished
			
				very frequently in firefox this would arise if you bound a 
				onFinished callback:
			
				controller = new AC.QuicktimeController(movie, {
					onMovieFinished: function() { ... }.bind(this) });
			*/
			
			return false;
		}
	},
	
	toggle: function() {
		
		if (this.isPlaying()) {
			this.Stop();
		} else {
			this.Play();
		}
		
	}

	
};
