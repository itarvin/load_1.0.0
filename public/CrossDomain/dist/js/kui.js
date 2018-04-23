/** 
 KevinUI v2.0.0
 Author:kevin
 HomePage:http://www.KevinUI.com
 */
!function(win,$){
	'use strict';
	var device = {};
    var ua = navigator.userAgent;
    var android = ua.match(/(Android);?[\s\/]+([\d.]+)?/);
    var ipad = ua.match(/(iPad).*OS\s([\d_]+)/);
    var ipod = ua.match(/(iPod)(.*OS\s([\d_]+))?/);
    var iphone = !ipad && ua.match(/(iPhone\sOS)\s([\d_]+)/);
	var local=window.location;
	
	device.os = device.version = null;
	if (android) {
		device.name=device.os='android';
		device.version=android[2];
	}
	if(ipad || iphone || ipod){
		device.os = 'ios';
		device.name=iphone?'iphone':ipad?'ipad':ipod?'ipod':'';
		device.version=(ipod?ipod[3]:(ipad|| iphone)[2]).replace(/_/g, '.');
	}
	//KUI属性
	win.kui={
		tap:'click',
		version:'1.2.0',
		ua:ua,
		device:device,
		isWeixin:/MicroMessenger/i.test(ua),
		appback:'',
		
	};
	
	//获取地址栏参数
	kui.Get=function (name){
		var reg = new RegExp("(^|&)" + name.replace(/#/g,'') + "=([^&]*)(&|$)", "i");
		var local=name.substr(0,1)=="#"?win.location.hash:win.location.search;
		var r = local.substr(1).match(reg);
		var context = "";
		if (r !== null) {
			context = r[2];
		}
		reg = null;
		r = null;
		return context === null || context === "" || context == "undefined" ? null: context;
	};
	//缓存
	kui.cache=function(name,value,time){
		if(typeof(name)!='string')return false;
		var fixname='cache_'+name,t=new Date().getTime(),data;
		if(value===undefined){
			data=eval('('+localStorage.getItem(fixname)+')')||{};
			if(data.time>0&&data.time<t){
				kui.removeCache(name);
				data={};
			}else{
				data=data;
			}
			return data.data;
		}else{
			time=isNaN(time)?0:time;
			var extime=time>0?time*60*60*1000+t:0;
			data={data:value,time:extime};
			localStorage.setItem(fixname,JSON.stringify(data));
			return value;
		}
		
	};
	//删除缓存
	kui.removeCache=function(name){
		var fixname='cache_'+name;
		if(name===undefined){
			$.each(localStorage,function(id,itm){
				if(id==fixname){
					kui.removeCache(name);
				}
			});
		}else{
			localStorage.removeItem('cache_'+name);
		}
	};
	//获取cookie
	kui.getCookie = function(name) {
		var arr = document.cookie.match(new RegExp("(^| )" + name + "=([^;]*)(;|$)"));
		if (arr !== null) return unescape(arr[2]);
		return null;
	};
	//删除cookie
	kui.delCookie = function(name) {
		name = escape(name);
		path = path? ';path=' + path:'';
		var expires = new Date(0);  
		document.cookie = name + "="+ ";expires=" + expires.toUTCString() + path; 
	};
	//添加cookie
	kui.addCookie = function(name, value, day, path) {
		var str = name + "=" + escape(value);
		var date = new Date();
		var ms = (day||30) * 24 * 3600 * 1000;
		date.setTime(date.getTime() + ms);
		str += ";path=" + (path || '/') + ";expires=" + date.toGMTString();
		document.cookie = str;
	};
	
	$(function(){
		FastClick.attach(document.body);
		
		//返回策略
		$(document).on('click','.app-back',function(){
			$.isFunction(kui.appback)?kui.appback():history.back();
		});
	});
	
}(window,Zepto);;;(function () {
    'use strict';

    /**
     * @preserve FastClick: polyfill to remove click delays on browsers with touch UIs.
     *
     * @codingstandard ftlabs-jsv2
     * @copyright The Financial Times Limited [All Rights Reserved]
     * @license MIT License (see LICENSE.txt)
     */

    /*jslint browser:true, node:true, elision:true*/
    /*global Event, Node*/


    /**
     * Instantiate fast-clicking listeners on the specified layer.
     *
     * @constructor
     * @param {Element} layer The layer to listen on
     * @param {Object} [options={}] The options to override the defaults
     */
    function FastClick(layer, options) {
        var oldOnClick;

        options = options || {};

        /**
         * Whether a click is currently being tracked.
         *
         * @type boolean
         */
        this.trackingClick = false;


        /**
         * Timestamp for when click tracking started.
         *
         * @type number
         */
        this.trackingClickStart = 0;


        /**
         * The element being tracked for a click.
         *
         * @type EventTarget
         */
        this.targetElement = null;


        /**
         * X-coordinate of touch start event.
         *
         * @type number
         */
        this.touchStartX = 0;


        /**
         * Y-coordinate of touch start event.
         *
         * @type number
         */
        this.touchStartY = 0;


        /**
         * ID of the last touch, retrieved from Touch.identifier.
         *
         * @type number
         */
        this.lastTouchIdentifier = 0;


        /**
         * Touchmove boundary, beyond which a click will be cancelled.
         *
         * @type number
         */
        this.touchBoundary = options.touchBoundary || 10;


        /**
         * The FastClick layer.
         *
         * @type Element
         */
        this.layer = layer;

        /**
         * The minimum time between tap(touchstart and touchend) events
         *
         * @type number
         */
        this.tapDelay = options.tapDelay || 200;

        /**
         * The maximum time for a tap
         *
         * @type number
         */
        this.tapTimeout = options.tapTimeout || 700;

        if (FastClick.notNeeded(layer)) {
            return;
        }

        // Some old versions of Android don't have Function.prototype.bind
        function bind(method, context) {
            return function() { return method.apply(context, arguments); };
        }


        var methods = ['onMouse', 'onClick', 'onTouchStart', 'onTouchMove', 'onTouchEnd', 'onTouchCancel'];
        var context = this;
        for (var i = 0, l = methods.length; i < l; i++) {
            context[methods[i]] = bind(context[methods[i]], context);
        }

        // Set up event handlers as required
        if (deviceIsAndroid) {
            layer.addEventListener('mouseover', this.onMouse, true);
            layer.addEventListener('mousedown', this.onMouse, true);
            layer.addEventListener('mouseup', this.onMouse, true);
        }

        layer.addEventListener('click', this.onClick, true);
        layer.addEventListener('touchstart', this.onTouchStart, false);
        layer.addEventListener('touchmove', this.onTouchMove, false);
        layer.addEventListener('touchend', this.onTouchEnd, false);
        layer.addEventListener('touchcancel', this.onTouchCancel, false);

        // Hack is required for browsers that don't support Event#stopImmediatePropagation (e.g. Android 2)
        // which is how FastClick normally stops click events bubbling to callbacks registered on the FastClick
        // layer when they are cancelled.
        if (!Event.prototype.stopImmediatePropagation) {
            layer.removeEventListener = function(type, callback, capture) {
                var rmv = Node.prototype.removeEventListener;
                if (type === 'click') {
                    rmv.call(layer, type, callback.hijacked || callback, capture);
                } else {
                    rmv.call(layer, type, callback, capture);
                }
            };

            layer.addEventListener = function(type, callback, capture) {
                var adv = Node.prototype.addEventListener;
                if (type === 'click') {
                    adv.call(layer, type, callback.hijacked || (callback.hijacked = function(event) {
                        if (!event.propagationStopped) {
                            callback(event);
                        }
                    }), capture);
                } else {
                    adv.call(layer, type, callback, capture);
                }
            };
        }

        // If a handler is already declared in the element's onclick attribute, it will be fired before
        // FastClick's onClick handler. Fix this by pulling out the user-defined handler function and
        // adding it as listener.
        if (typeof layer.onclick === 'function') {

            // Android browser on at least 3.2 requires a new reference to the function in layer.onclick
            // - the old one won't work if passed to addEventListener directly.
            oldOnClick = layer.onclick;
            layer.addEventListener('click', function(event) {
                oldOnClick(event);
            }, false);
            layer.onclick = null;
        }
    }

    /**
     * Windows Phone 8.1 fakes user agent string to look like Android and iPhone.
     *
     * @type boolean
     */
    var deviceIsWindowsPhone = navigator.userAgent.indexOf("Windows Phone") >= 0;

    /**
     * Android requires exceptions.
     *
     * @type boolean
     */
    var deviceIsAndroid = navigator.userAgent.indexOf('Android') > 0 && !deviceIsWindowsPhone;


    /**
     * iOS requires exceptions.
     *
     * @type boolean
     */
    var deviceIsIOS = /iP(ad|hone|od)/.test(navigator.userAgent) && !deviceIsWindowsPhone;


    /**
     * iOS 4 requires an exception for select elements.
     *
     * @type boolean
     */
    var deviceIsIOS4 = deviceIsIOS && (/OS 4_\d(_\d)?/).test(navigator.userAgent);


    /**
     * iOS 6.0-7.* requires the target element to be manually derived
     *
     * @type boolean
     */
    var deviceIsIOSWithBadTarget = deviceIsIOS && (/OS [6-7]_\d/).test(navigator.userAgent);

    /**
     * BlackBerry requires exceptions.
     *
     * @type boolean
     */
    var deviceIsBlackBerry10 = navigator.userAgent.indexOf('BB10') > 0;

    /**
     * 判断是否组合型label
     * @type {Boolean}
     */
    var isCompositeLabel = false;

    /**
     * Determine whether a given element requires a native click.
     *
     * @param {EventTarget|Element} target Target DOM element
     * @returns {boolean} Returns true if the element needs a native click
     */
    FastClick.prototype.needsClick = function(target) {

        // 修复bug: 如果父元素中有 label
        // 如果label上有needsclick这个类，则用原生的点击，否则，用模拟点击
        var parent = target;
        while(parent && (parent.tagName.toUpperCase() !== "BODY")) {
            if (parent.tagName.toUpperCase() === "LABEL") {
                isCompositeLabel = true;
                if ((/\bneedsclick\b/).test(parent.className)) return true;
            }
            parent = parent.parentNode;
        }

        switch (target.nodeName.toLowerCase()) {

            // Don't send a synthetic click to disabled inputs (issue #62)
            case 'button':
            case 'select':
            case 'textarea':
                if (target.disabled) {
                    return true;
                }

                break;
            case 'input':

                // File inputs need real clicks on iOS 6 due to a browser bug (issue #68)
                if ((deviceIsIOS && target.type === 'file') || target.disabled) {
                    return true;
                }

                break;
            case 'label':
            case 'iframe': // iOS8 homescreen apps can prevent events bubbling into frames
            case 'video':
                return true;
        }

        return (/\bneedsclick\b/).test(target.className);
    };


    /**
     * Determine whether a given element requires a call to focus to simulate click into element.
     *
     * @param {EventTarget|Element} target Target DOM element
     * @returns {boolean} Returns true if the element requires a call to focus to simulate native click.
     */
    FastClick.prototype.needsFocus = function(target) {
        switch (target.nodeName.toLowerCase()) {
            case 'textarea':
                return true;
            case 'select':
                return !deviceIsAndroid;
            case 'input':
                switch (target.type) {
                    case 'button':
                    case 'checkbox':
                    case 'file':
                    case 'image':
                    case 'radio':
                    case 'submit':
                        return false;
                }

                // No point in attempting to focus disabled inputs
                return !target.disabled && !target.readOnly;
            default:
                return (/\bneedsfocus\b/).test(target.className);
        }
    };


    /**
     * Send a click event to the specified element.
     *
     * @param {EventTarget|Element} targetElement
     * @param {Event} event
     */
    FastClick.prototype.sendClick = function(targetElement, event) {
        var clickEvent, touch;

        // On some Android devices activeElement needs to be blurred otherwise the synthetic click will have no effect (#24)
        if (document.activeElement && document.activeElement !== targetElement) {
            document.activeElement.blur();
        }

        touch = event.changedTouches[0];

        // Synthesise a click event, with an extra attribute so it can be tracked
        clickEvent = document.createEvent('MouseEvents');
        clickEvent.initMouseEvent(this.determineEventType(targetElement), true, true, window, 1, touch.screenX, touch.screenY, touch.clientX, touch.clientY, false, false, false, false, 0, null);
        clickEvent.forwardedTouchEvent = true;
        targetElement.dispatchEvent(clickEvent);
    };

    FastClick.prototype.determineEventType = function(targetElement) {

        //Issue #159: Android Chrome Select Box does not open with a synthetic click event
        if (deviceIsAndroid && targetElement.tagName.toLowerCase() === 'select') {
            return 'mousedown';
        }

        return 'click';
    };


    /**
     * @param {EventTarget|Element} targetElement
     */
    FastClick.prototype.focus = function(targetElement) {
        var length;

        // Issue #160: on iOS 7, some input elements (e.g. date datetime month) throw a vague TypeError on setSelectionRange. These elements don't have an integer value for the selectionStart and selectionEnd properties, but unfortunately that can't be used for detection because accessing the properties also throws a TypeError. Just check the type instead. Filed as Apple bug #15122724.
        var unsupportedType = ['date', 'time', 'month', 'number', 'email'];
        if (deviceIsIOS && targetElement.setSelectionRange && unsupportedType.indexOf(targetElement.type) === -1) {
            length = targetElement.value.length;
            targetElement.setSelectionRange(length, length);
        } else {
            targetElement.focus();
        }
    };


    /**
     * Check whether the given target element is a child of a scrollable layer and if so, set a flag on it.
     *
     * @param {EventTarget|Element} targetElement
     */
    FastClick.prototype.updateScrollParent = function(targetElement) {
        var scrollParent, parentElement;

        scrollParent = targetElement.fastClickScrollParent;

        // Attempt to discover whether the target element is contained within a scrollable layer. Re-check if the
        // target element was moved to another parent.
        if (!scrollParent || !scrollParent.contains(targetElement)) {
            parentElement = targetElement;
            do {
                if (parentElement.scrollHeight > parentElement.offsetHeight) {
                    scrollParent = parentElement;
                    targetElement.fastClickScrollParent = parentElement;
                    break;
                }

                parentElement = parentElement.parentElement;
            } while (parentElement);
        }

        // Always update the scroll top tracker if possible.
        if (scrollParent) {
            scrollParent.fastClickLastScrollTop = scrollParent.scrollTop;
        }
    };


    /**
     * @param {EventTarget} targetElement
     * @returns {Element|EventTarget}
     */
    FastClick.prototype.getTargetElementFromEventTarget = function(eventTarget) {

        // On some older browsers (notably Safari on iOS 4.1 - see issue #56) the event target may be a text node.
        if (eventTarget.nodeType === Node.TEXT_NODE) {
            return eventTarget.parentNode;
        }

        return eventTarget;
    };


    /**
     * On touch start, record the position and scroll offset.
     *
     * @param {Event} event
     * @returns {boolean}
     */
    FastClick.prototype.onTouchStart = function(event) {
        var targetElement, touch, selection;

        // Ignore multiple touches, otherwise pinch-to-zoom is prevented if both fingers are on the FastClick element (issue #111).
        if (event.targetTouches.length > 1) {
            return true;
        }

        targetElement = this.getTargetElementFromEventTarget(event.target);
        touch = event.targetTouches[0];

        if (deviceIsIOS) {

            // Only trusted events will deselect text on iOS (issue #49)
            selection = window.getSelection();
            if (selection.rangeCount && !selection.isCollapsed) {
                return true;
            }

            if (!deviceIsIOS4) {

                // Weird things happen on iOS when an alert or confirm dialog is opened from a click event callback (issue #23):
                // when the user next taps anywhere else on the page, new touchstart and touchend events are dispatched
                // with the same identifier as the touch event that previously triggered the click that triggered the alert.
                // Sadly, there is an issue on iOS 4 that causes some normal touch events to have the same identifier as an
                // immediately preceeding touch event (issue #52), so this fix is unavailable on that platform.
                // Issue 120: touch.identifier is 0 when Chrome dev tools 'Emulate touch events' is set with an iOS device UA string,
                // which causes all touch events to be ignored. As this block only applies to iOS, and iOS identifiers are always long,
                // random integers, it's safe to to continue if the identifier is 0 here.
                if (touch.identifier && touch.identifier === this.lastTouchIdentifier) {
                    event.preventDefault();
                    return false;
                }

                this.lastTouchIdentifier = touch.identifier;

                // If the target element is a child of a scrollable layer (using -webkit-overflow-scrolling: touch) and:
                // 1) the user does a fling scroll on the scrollable layer
                // 2) the user stops the fling scroll with another tap
                // then the event.target of the last 'touchend' event will be the element that was under the user's finger
                // when the fling scroll was started, causing FastClick to send a click event to that layer - unless a check
                // is made to ensure that a parent layer was not scrolled before sending a synthetic click (issue #42).
                this.updateScrollParent(targetElement);
            }
        }

        this.trackingClick = true;
        this.trackingClickStart = event.timeStamp;
        this.targetElement = targetElement;

        this.touchStartX = touch.pageX;
        this.touchStartY = touch.pageY;

        // Prevent phantom clicks on fast double-tap (issue #36)
        if ((event.timeStamp - this.lastClickTime) < this.tapDelay) {
            event.preventDefault();
        }

        return true;
    };


    /**
     * Based on a touchmove event object, check whether the touch has moved past a boundary since it started.
     *
     * @param {Event} event
     * @returns {boolean}
     */
    FastClick.prototype.touchHasMoved = function(event) {
        var touch = event.changedTouches[0], boundary = this.touchBoundary;

        if (Math.abs(touch.pageX - this.touchStartX) > boundary || Math.abs(touch.pageY - this.touchStartY) > boundary) {
            return true;
        }

        return false;
    };


    /**
     * Update the last position.
     *
     * @param {Event} event
     * @returns {boolean}
     */
    FastClick.prototype.onTouchMove = function(event) {
        if (!this.trackingClick) {
            return true;
        }

        // If the touch has moved, cancel the click tracking
        if (this.targetElement !== this.getTargetElementFromEventTarget(event.target) || this.touchHasMoved(event)) {
            this.trackingClick = false;
            this.targetElement = null;
        }

        return true;
    };


    /**
     * Attempt to find the labelled control for the given label element.
     *
     * @param {EventTarget|HTMLLabelElement} labelElement
     * @returns {Element|null}
     */
    FastClick.prototype.findControl = function(labelElement) {

        // Fast path for newer browsers supporting the HTML5 control attribute
        if (labelElement.control !== undefined) {
            return labelElement.control;
        }

        // All browsers under test that support touch events also support the HTML5 htmlFor attribute
        if (labelElement.htmlFor) {
            return document.getElementById(labelElement.htmlFor);
        }

        // If no for attribute exists, attempt to retrieve the first labellable descendant element
        // the list of which is defined here: http://www.w3.org/TR/html5/forms.html#category-label
        return labelElement.querySelector('button, input:not([type=hidden]), keygen, meter, output, progress, select, textarea');
    };


    /**
     * On touch end, determine whether to send a click event at once.
     *
     * @param {Event} event
     * @returns {boolean}
     */
    FastClick.prototype.onTouchEnd = function(event) {
        var forElement, trackingClickStart, targetTagName, scrollParent, touch, targetElement = this.targetElement;

        if (!this.trackingClick) {
            return true;
        }

        // Prevent phantom clicks on fast double-tap (issue #36)
        if ((event.timeStamp - this.lastClickTime) < this.tapDelay) {
            this.cancelNextClick = true;
            return true;
        }

        if ((event.timeStamp - this.trackingClickStart) > this.tapTimeout) {
            return true;
        }
        //修复安卓微信下，input type="date" 的bug，经测试date,time,month已没问题
        var unsupportedType = ['date', 'time', 'month'];
        if(unsupportedType.indexOf(event.target.type) !== -1){
            　　　　return false;
            　　}
        // Reset to prevent wrong click cancel on input (issue #156).
        this.cancelNextClick = false;

        this.lastClickTime = event.timeStamp;

        trackingClickStart = this.trackingClickStart;
        this.trackingClick = false;
        this.trackingClickStart = 0;

        // On some iOS devices, the targetElement supplied with the event is invalid if the layer
        // is performing a transition or scroll, and has to be re-detected manually. Note that
        // for this to function correctly, it must be called *after* the event target is checked!
        // See issue #57; also filed as rdar://13048589 .
        if (deviceIsIOSWithBadTarget) {
            touch = event.changedTouches[0];

            // In certain cases arguments of elementFromPoint can be negative, so prevent setting targetElement to null
            targetElement = document.elementFromPoint(touch.pageX - window.pageXOffset, touch.pageY - window.pageYOffset) || targetElement;
            targetElement.fastClickScrollParent = this.targetElement.fastClickScrollParent;
        }

        targetTagName = targetElement.tagName.toLowerCase();
        if (targetTagName === 'label') {
            forElement = this.findControl(targetElement);
            if (forElement) {
                this.focus(targetElement);
                if (deviceIsAndroid) {
                    return false;
                }

                targetElement = forElement;
            }
        } else if (this.needsFocus(targetElement)) {

            // Case 1: If the touch started a while ago (best guess is 100ms based on tests for issue #36) then focus will be triggered anyway. Return early and unset the target element reference so that the subsequent click will be allowed through.
            // Case 2: Without this exception for input elements tapped when the document is contained in an iframe, then any inputted text won't be visible even though the value attribute is updated as the user types (issue #37).
            if ((event.timeStamp - trackingClickStart) > 100 || (deviceIsIOS && window.top !== window && targetTagName === 'input')) {
                this.targetElement = null;
                return false;
            }

            this.focus(targetElement);
            this.sendClick(targetElement, event);

            // Select elements need the event to go through on iOS 4, otherwise the selector menu won't open.
            // Also this breaks opening selects when VoiceOver is active on iOS6, iOS7 (and possibly others)
            if (!deviceIsIOS || targetTagName !== 'select') {
                this.targetElement = null;
                event.preventDefault();
            }

            return false;
        }

        if (deviceIsIOS && !deviceIsIOS4) {

            // Don't send a synthetic click event if the target element is contained within a parent layer that was scrolled
            // and this tap is being used to stop the scrolling (usually initiated by a fling - issue #42).
            scrollParent = targetElement.fastClickScrollParent;
            if (scrollParent && scrollParent.fastClickLastScrollTop !== scrollParent.scrollTop) {
                return true;
            }
        }

        // Prevent the actual click from going though - unless the target node is marked as requiring
        // real clicks or if it is in the whitelist in which case only non-programmatic clicks are permitted.
        if (!this.needsClick(targetElement)) {
            event.preventDefault();
            this.sendClick(targetElement, event);
        }

        return false;
    };


    /**
     * On touch cancel, stop tracking the click.
     *
     * @returns {void}
     */
    FastClick.prototype.onTouchCancel = function() {
        this.trackingClick = false;
        this.targetElement = null;
    };


    /**
     * Determine mouse events which should be permitted.
     *
     * @param {Event} event
     * @returns {boolean}
     */
    FastClick.prototype.onMouse = function(event) {

        // If a target element was never set (because a touch event was never fired) allow the event
        if (!this.targetElement) {
            return true;
        }

        if (event.forwardedTouchEvent) {
            return true;
        }

        // Programmatically generated events targeting a specific element should be permitted
        if (!event.cancelable) {
            return true;
        }

        // Derive and check the target element to see whether the mouse event needs to be permitted;
        // unless explicitly enabled, prevent non-touch click events from triggering actions,
        // to prevent ghost/doubleclicks.
        if (!this.needsClick(this.targetElement) || this.cancelNextClick) {

            // Prevent any user-added listeners declared on FastClick element from being fired.
            if (event.stopImmediatePropagation) {
                event.stopImmediatePropagation();
            } else {

                // Part of the hack for browsers that don't support Event#stopImmediatePropagation (e.g. Android 2)
                event.propagationStopped = true;
            }

            // Cancel the event
            event.stopPropagation();
            // 允许组合型label冒泡
            if (!isCompositeLabel) {
                event.preventDefault();
            }
            // 允许组合型label冒泡
            return false;
        }

        // If the mouse event is permitted, return true for the action to go through.
        return true;
    };


    /**
     * On actual clicks, determine whether this is a touch-generated click, a click action occurring
     * naturally after a delay after a touch (which needs to be cancelled to avoid duplication), or
     * an actual click which should be permitted.
     *
     * @param {Event} event
     * @returns {boolean}
     */
    FastClick.prototype.onClick = function(event) {
        var permitted;

        // It's possible for another FastClick-like library delivered with third-party code to fire a click event before FastClick does (issue #44). In that case, set the click-tracking flag back to false and return early. This will cause onTouchEnd to return early.
        if (this.trackingClick) {
            this.targetElement = null;
            this.trackingClick = false;
            return true;
        }

        // Very odd behaviour on iOS (issue #18): if a submit element is present inside a form and the user hits enter in the iOS simulator or clicks the Go button on the pop-up OS keyboard the a kind of 'fake' click event will be triggered with the submit-type input element as the target.
        if (event.target.type === 'submit' && event.detail === 0) {
            return true;
        }

        permitted = this.onMouse(event);

        // Only unset targetElement if the click is not permitted. This will ensure that the check for !targetElement in onMouse fails and the browser's click doesn't go through.
        if (!permitted) {
            this.targetElement = null;
        }

        // If clicks are permitted, return true for the action to go through.
        return permitted;
    };


    /**
     * Remove all FastClick's event listeners.
     *
     * @returns {void}
     */
    FastClick.prototype.destroy = function() {
        var layer = this.layer;

        if (deviceIsAndroid) {
            layer.removeEventListener('mouseover', this.onMouse, true);
            layer.removeEventListener('mousedown', this.onMouse, true);
            layer.removeEventListener('mouseup', this.onMouse, true);
        }

        layer.removeEventListener('click', this.onClick, true);
        layer.removeEventListener('touchstart', this.onTouchStart, false);
        layer.removeEventListener('touchmove', this.onTouchMove, false);
        layer.removeEventListener('touchend', this.onTouchEnd, false);
        layer.removeEventListener('touchcancel', this.onTouchCancel, false);
    };


    /**
     * Check whether FastClick is needed.
     *
     * @param {Element} layer The layer to listen on
     */
    FastClick.notNeeded = function(layer) {
        var metaViewport;
        var chromeVersion;
        var blackberryVersion;
        var firefoxVersion;

        // Devices that don't support touch don't need FastClick
        if (typeof window.ontouchstart === 'undefined') {
            return true;
        }

        // Chrome version - zero for other browsers
        chromeVersion = +(/Chrome\/([0-9]+)/.exec(navigator.userAgent) || [,0])[1];

        if (chromeVersion) {

            if (deviceIsAndroid) {
                metaViewport = document.querySelector('meta[name=viewport]');

                if (metaViewport) {
                    // Chrome on Android with user-scalable="no" doesn't need FastClick (issue #89)
                    if (metaViewport.content.indexOf('user-scalable=no') !== -1) {
                        return true;
                    }
                    // Chrome 32 and above with width=device-width or less don't need FastClick
                    if (chromeVersion > 31 && document.documentElement.scrollWidth <= window.outerWidth) {
                        return true;
                    }
                }

                // Chrome desktop doesn't need FastClick (issue #15)
            } else {
                return true;
            }
        }

        if (deviceIsBlackBerry10) {
            blackberryVersion = navigator.userAgent.match(/Version\/([0-9]*)\.([0-9]*)/);

            // BlackBerry 10.3+ does not require Fastclick library.
            // https://github.com/ftlabs/fastclick/issues/251
            if (blackberryVersion[1] >= 10 && blackberryVersion[2] >= 3) {
                metaViewport = document.querySelector('meta[name=viewport]');

                if (metaViewport) {
                    // user-scalable=no eliminates click delay.
                    if (metaViewport.content.indexOf('user-scalable=no') !== -1) {
                        return true;
                    }
                    // width=device-width (or less than device-width) eliminates click delay.
                    if (document.documentElement.scrollWidth <= window.outerWidth) {
                        return true;
                    }
                }
            }
        }

        // IE10 with -ms-touch-action: none or manipulation, which disables double-tap-to-zoom (issue #97)
        if (layer.style.msTouchAction === 'none' || layer.style.touchAction === 'manipulation') {
            return true;
        }

        // Firefox version - zero for other browsers
        firefoxVersion = +(/Firefox\/([0-9]+)/.exec(navigator.userAgent) || [,0])[1];

        if (firefoxVersion >= 27) {
            // Firefox 27+ does not have tap delay if the content is not zoomable - https://bugzilla.mozilla.org/show_bug.cgi?id=922896

            metaViewport = document.querySelector('meta[name=viewport]');
            if (metaViewport && (metaViewport.content.indexOf('user-scalable=no') !== -1 || document.documentElement.scrollWidth <= window.outerWidth)) {
                return true;
            }
        }

        // IE11: prefixed -ms-touch-action is no longer supported and it's recomended to use non-prefixed version
        // http://msdn.microsoft.com/en-us/library/windows/apps/Hh767313.aspx
        if (layer.style.touchAction === 'none' || layer.style.touchAction === 'manipulation') {
            return true;
        }

        return false;
    };


    /**
     * Factory method for creating a FastClick object
     *
     * @param {Element} layer The layer to listen on
     * @param {Object} [options={}] The options to override the defaults
     */
    FastClick.attach = function(layer, options) {
        return new FastClick(layer, options);
    };

    window.FastClick = FastClick;
}());
;(function($) {
    "use strict";
    ['width', 'height'].forEach(function(dimension) {
        var  Dimension = dimension.replace(/./, function(m) {
            return m[0].toUpperCase();
        });
        $.fn['outer' + Dimension] = function(margin) {
            var elem = this;
            if (elem) {
                var size = elem[dimension]();
                var sides = {
                    'width': ['left', 'right'],
                    'height': ['top', 'bottom']
                };
                sides[dimension].forEach(function(side) {
                    if (margin) size += parseInt(elem.css('margin-' + side), 10);
                });
                return size;
            } else {
                return null;
            }
        };
    });

    //support
    $.support = (function() {
        var support = {
            touch: !!(('ontouchstart' in window) || window.DocumentTouch && document instanceof window.DocumentTouch)
        };
        return support;
    })();

    $.touchEvents = {
        start: $.support.touch ? 'touchstart' : 'mousedown',
        move: $.support.touch ? 'touchmove' : 'mousemove',
        end: $.support.touch ? 'touchend' : 'mouseup'
    };

    $.getTranslate = function (el, axis) {
        var matrix, curTransform, curStyle, transformMatrix;

        // automatic axis detection
        if (typeof axis === 'undefined') {
            axis = 'x';
        }

        curStyle = window.getComputedStyle(el, null);
        if (window.WebKitCSSMatrix) {
            // Some old versions of Webkit choke when 'none' is passed; pass
            // empty string instead in this case
            transformMatrix = new WebKitCSSMatrix(curStyle.webkitTransform === 'none' ? '' : curStyle.webkitTransform);
        }
        else {
            transformMatrix = curStyle.MozTransform || curStyle.transform || curStyle.getPropertyValue('transform').replace('translate(', 'matrix(1, 0, 0, 1,');
            matrix = transformMatrix.toString().split(',');
        }

        if (axis === 'x') {
            //Latest Chrome and webkits Fix
            if (window.WebKitCSSMatrix)
                curTransform = transformMatrix.m41;
            //Crazy IE10 Matrix
            else if (matrix.length === 16)
                curTransform = parseFloat(matrix[12]);
            //Normal Browsers
            else
                curTransform = parseFloat(matrix[4]);
        }
        if (axis === 'y') {
            //Latest Chrome and webkits Fix
            if (window.WebKitCSSMatrix)
                curTransform = transformMatrix.m42;
            //Crazy IE10 Matrix
            else if (matrix.length === 16)
                curTransform = parseFloat(matrix[13]);
            //Normal Browsers
            else
                curTransform = parseFloat(matrix[5]);
        }

        return curTransform || 0;
    };
    /* jshint ignore:start */
    $.requestAnimationFrame = function (callback) {
        if (window.requestAnimationFrame) return window.requestAnimationFrame(callback);
        else if (window.webkitRequestAnimationFrame) return window.webkitRequestAnimationFrame(callback);
        else if (window.mozRequestAnimationFrame) return window.mozRequestAnimationFrame(callback);
        else {
            return window.setTimeout(callback, 1000 / 60);
        }
    };
    $.cancelAnimationFrame = function (id) {
        if (window.cancelAnimationFrame) return window.cancelAnimationFrame(id);
        else if (window.webkitCancelAnimationFrame) return window.webkitCancelAnimationFrame(id);
        else if (window.mozCancelAnimationFrame) return window.mozCancelAnimationFrame(id);
        else {
            return window.clearTimeout(id);
        }
    };
    /* jshint ignore:end */

    $.fn.dataset = function() {
        var dataset = {},
            ds = this[0].dataset;
        for (var key in ds) { // jshint ignore:line
            var item = (dataset[key] = ds[key]);
            if (item === 'false') dataset[key] = false;
            else if (item === 'true') dataset[key] = true;
            else if (parseFloat(item) === item * 1) dataset[key] = item * 1;
        }
        // mixin dataset and __eleData
        return $.extend({}, dataset, this[0].__eleData);
    };
    $.fn.data = function(key, value) {
        var tmpData = $(this).dataset();
        if (!key) {
            return tmpData;
        }
        // value may be 0, false, null
        if (typeof value === 'undefined') {
            // Get value
            var dataVal = tmpData[key],
                __eD = this[0].__eleData;

            //if (dataVal !== undefined) {
            if (__eD && (key in __eD)) {
                return __eD[key];
            } else {
                return dataVal;
            }

        } else {
            // Set value,uniformly set in extra ```__eleData```
            for (var i = 0; i < this.length; i++) {
                var el = this[i];
                // delete multiple data in dataset
                if (key in tmpData) delete el.dataset[key];

                if (!el.__eleData) el.__eleData = {};
                el.__eleData[key] = value;
            }
            return this;
        }
    };
    function __dealCssEvent(eventNameArr, callback) {
        var events = eventNameArr,
            i, dom = this;// jshint ignore:line

        function fireCallBack(e) {
            /*jshint validthis:true */
            if (e.target !== this) return;
            callback.call(this, e);
            for (i = 0; i < events.length; i++) {
                dom.off(events[i], fireCallBack);
            }
        }
        if (callback) {
            for (i = 0; i < events.length; i++) {
                dom.on(events[i], fireCallBack);
            }
        }
    }
    $.fn.animationEnd = function(callback) {
        __dealCssEvent.call(this, ['webkitAnimationEnd', 'animationend'], callback);
        return this;
    };
    $.fn.transitionEnd = function(callback) {
        __dealCssEvent.call(this, ['webkitTransitionEnd', 'transitionend'], callback);
        return this;
    };
    $.fn.transition = function(duration) {
        if (typeof duration !== 'string') {
            duration = duration + 'ms';
        }
        for (var i = 0; i < this.length; i++) {
            var elStyle = this[i].style;
            elStyle.webkitTransitionDuration = elStyle.MozTransitionDuration = elStyle.transitionDuration = duration;
        }
        return this;
    };
    $.fn.transform = function(transform) {
        for (var i = 0; i < this.length; i++) {
            var elStyle = this[i].style;
            elStyle.webkitTransform = elStyle.MozTransform = elStyle.transform = transform;
        }
        return this;
    };
    $.fn.prevAll = function (selector) {
        var prevEls = [];
        var el = this[0];
        if (!el) return $([]);
        while (el.previousElementSibling) {
            var prev = el.previousElementSibling;
            if (selector) {
                if($(prev).is(selector)) prevEls.push(prev);
            }
            else prevEls.push(prev);
            el = prev;
        }
        return $(prevEls);
    };
    $.fn.nextAll = function (selector) {
        var nextEls = [];
        var el = this[0];
        if (!el) return $([]);
        while (el.nextElementSibling) {
            var next = el.nextElementSibling;
            if (selector) {
                if($(next).is(selector)) nextEls.push(next);
            }
            else nextEls.push(next);
            el = next;
        }
        return $(nextEls);
    };

    //重置zepto的show方法，防止有些人引用的版本中 show 方法操作 opacity 属性影响动画执行
    $.fn.show = function(){
        var elementDisplay = {};
        function defaultDisplay(nodeName) {
            var element, display;
            if (!elementDisplay[nodeName]) {
                element = document.createElement(nodeName);
                document.body.appendChild(element);
                display = getComputedStyle(element, '').getPropertyValue("display");
                element.parentNode.removeChild(element);
                display === "none" && (display = "block");
                elementDisplay[nodeName] = display;
            }
            return elementDisplay[nodeName];
        }

        return this.each(function(){
            this.style.display === "none" && (this.style.display = '');
            if (getComputedStyle(this, '').getPropertyValue("display") === "none");
            this.style.display = defaultDisplay(this.nodeName);
        });
    };
	
	//Zepto的$.getScript扩展
	$.getScript=function(src,func){
		var script=document.createElement('script');
		script.async="async";
		script.src=src;
		if(func){
			script.onload=func;
		}
		document.getElementsByTagName("head")[0].appendChild(script);
	};
})(Zepto);
;/*! JRoll v2.5.0 ~ (c) 2015-2017 Author:BarZu Git:https://github.com/chjtx/JRoll Website:http://www.chjtx.com/JRoll/ */
/* global define, HTMLElement */
(function (window, document, Math) {
  'use strict'

  var JRoll
  var VERSION = '2.5.0'
  var rAF = window.requestAnimationFrame || window.webkitRequestAnimationFrame || function (callback) {
    setTimeout(callback, 17)
  }
  var sty = document.createElement('div').style
  var jrollMap = {} // 保存所有JRoll对象
  var ua = navigator.userAgent.toLowerCase()
  var prefix = (function () {
    var vendors = ['OT', 'msT', 'MozT', 'webkitT', 't']
    var transform
    var i = vendors.length

    while (i--) {
      transform = vendors[i] + 'ransform'
      if (transform in sty) return vendors[i]
    }
  })()

  // 实用工具
  var utils = {
    // 兼容
    TSF: prefix + 'ransform',
    TSD: prefix + 'ransitionDuration',
    TFO: prefix + 'ransformOrigin',
    isAndroid: /android/.test(ua),
    isIOS: /iphone|ipad/.test(ua),
    isMobile: /mobile|phone|android|pad/.test(ua),

    // 判断浏览是否支持perspective属性，从而判断是否支持开启3D加速
    translateZ: (function (pre) {
      var f
      if (pre) {
        f = pre + 'Perspective' in sty
      } else {
        f = 'perspective' in sty
      }
      return f ? ' translateZ(0px)' : ''
    })(prefix.substr(0, prefix.length - 1)),

    // 计算相对偏移，a相对于b的偏移
    computeTranslate: function (a, b) {
      var x = 0
      var y = 0
      var s
      while (a) {
        s = window.getComputedStyle(a)[utils.TSF].replace(/matrix\(|\)/g, '').split(', ')
        x += parseInt(s[4]) || 0
        y += parseInt(s[5]) || 0
        a = a.parentElement
        if (a === b) {
          a = null
        }
      }
      return {
        x: x,
        y: y
      }
    },

    // 计算相对位置，a相对于b的位置
    computePosition: function (a, b) {
      var left = 0
      var top = 0
      while (a) {
        left += a.offsetLeft
        top += a.offsetTop
        a = a.offsetParent
        if (a === b) {
          a = null
        }
      }
      return {
        left: left,
        top: top
      }
    },

    /**
     * 在指定时间内将指定元素从开始位置移到结束位置并执行回调方法
     * el 必须是dom元素，必填
     * x,y 结束位置，必填
     * duration 过渡时长，单位ms，可选
     * callback 回调方法，可选
     */
    moveTo: function (el, x, y, duration, callback) {
      var startX = 0
      var startY = 0
      var endX
      var endY
      var zoom = 1
      var stepX
      var stepY
      var d
      var result
      result = /translate\(([-\d.]+)px,\s+([-\d.]+)px\)\s+(?:translateZ\(0px\)\s+)?scale\(([\d.]+)\)/.exec(el.style[utils.TSF])
      if (result) {
        startX = Number(result[1])
        startY = Number(result[2])
        zoom = Number(result[3])
      }
      d = duration || 17
      stepX = (x - startX) / (d / 17)
      stepY = (y - startY) / (d / 17)
      endX = startX
      endY = startY

      function moving () {
        d = d - 17
        if (d <= 0) {
          endX = x
          endY = y
        } else {
          endX = parseInt(endX + stepX, 10)
          endY = parseInt(endY + stepY, 10)
        }
        el.style[utils.TSF] = 'translate(' + endX + 'px, ' + endY + 'px)' + utils.translateZ + ' scale(' + zoom + ')'

        if (d > 0 && !(endX === x && endY === y)) {
          rAF(moving)
        } else if (typeof callback === 'function') {
          callback()
        }
      }

      moving()
    },

    /**
     * 一层一层往上查找已实例化的jroll
     * el 目标元素
     * force 强制查找，忽略textarea
     */
    findScroller: function (el, force) {
      var id
      // 遇到document或带垂直滚动条的textarea终止查找
      if (force || !(el.tagName === 'TEXTAREA' && el.scrollHeight > el.offsetHeight)) {
        while (el !== document) {
          id = el.getAttribute('jroll-id')
          if (id) {
            return jrollMap[id]
          }
          el = el.parentNode
        }
      }
      return null
    },
    // 一层一层往上查找所有已实例化的jroll
    findAllJRolls: function (el, force) {
      var jrolls = []
      var id
      // 遇到document或带垂直滚动条的textarea终止查找
      if (force || !(el.tagName === 'TEXTAREA' && (el.scrollHeight > el.clientHeight) && (el.scrollTop > 0 && el.scrollTop < el.scrollHeight - el.clientHeight))) {
        while (el !== document) {
          id = el.getAttribute('jroll-id')
          if (id) {
            jrolls.push(jrollMap[id])
          }
          el = el.parentNode
        }
      }
      return jrolls
    }
  }

  function _touchstart (e) {
    var jrolls = utils.findAllJRolls(e.target)
    var l = jrolls.length

    // 非缩放且第二个手指按屏中止往后执行
    if (JRoll.jrollActive && !JRoll.jrollActive.options.zoom && e.touches && e.touches.length > 1) {
      return
    }
    if (l) {
      while (l--) {
        if (jrolls[l].moving) {
          e.preventDefault() // 防止按停滑动时误触a链接
          jrolls[l]._endAction() // 结束并终止惯性
        }
      }

      JRoll.jrollActive = jrolls[0]
      JRoll.jrollActive._start(e)
    } else if (JRoll.jrollActive) {
      JRoll.jrollActive._end(e)
    }
  }

  function _touchmove (e) {
    if (JRoll.jrollActive) {
      var activeElement = document.activeElement
      if (JRoll.jrollActive.options.preventDefault) {
        e.preventDefault()
      }
      if (utils.isMobile && JRoll.jrollActive.options.autoBlur && (activeElement.tagName === 'INPUT' || activeElement.tagName === 'TEXTAREA')) {
        activeElement.blur()
      }
      JRoll.jrollActive._move(e)
    }
  }

  function _touchend (e) {
    if (JRoll.jrollActive) {
      JRoll.jrollActive._end(e)
    }
  }

  function _resize () {
    setTimeout(function () {
      for (var i in jrollMap) {
        jrollMap[i].refresh().scrollTo(jrollMap[i].x, jrollMap[i].y, 200)
      }
    }, 600)
  }

  function _wheel (e) {
    var jroll = utils.findScroller(e.target)
    if (jroll) {
      jroll._wheel(e)
    }
  }

  // 检测是否支持passive选项
  var supportsPassiveOption = false
  try {
    var opts = Object.defineProperty({}, 'passive', {
      get: function () {
        supportsPassiveOption = true
      }
    })
    window.addEventListener('test', null, opts)
  } catch (e) {}

  function addEvent (type, method) {
    document.addEventListener(type, method, supportsPassiveOption ? { passive: false } : false)
  }

  // 添加监听事件
  addEvent(utils.isMobile ? 'touchstart' : 'mousedown', _touchstart)
  addEvent(utils.isMobile ? 'touchmove' : 'mousemove', _touchmove)
  addEvent(utils.isMobile ? 'touchend' : 'mouseup', _touchend)
  if (utils.isMobile) {
    addEvent('touchcancel', _touchend)
  } else {
    addEvent(/firefox/.test(ua) ? 'DOMMouseScroll' : 'mousewheel', _wheel)
  }
  window.addEventListener('resize', _resize)
  window.addEventListener('orientationchange', _resize)

  JRoll = function (el, options) {
    var me = this

    me.wrapper = typeof el === 'string' ? document.querySelector(el) : el
    me.scroller = options && options.scroller ? (typeof options.scroller === 'string' ? document.querySelector(options.scroller) : options.scroller) : me.wrapper.children[0]

    // 防止重复多次new JRoll
    if (me.scroller.jroll) {
      me.scroller.jroll.refresh()
      return me.scroller.jroll
    } else {
      me.scroller.jroll = me
    }

    this._init(el, options)
  }

  JRoll.version = VERSION

  JRoll.utils = utils

  JRoll.jrollMap = jrollMap

  JRoll.prototype = {
    // 初始化
    _init: function (el, options) {
      var me = this

      // 计算wrapper相对document的位置
      me.wrapperOffset = utils.computePosition(me.wrapper, document.body)

      // 创建ID
      me.id = (options && options.id) || me.scroller.getAttribute('jroll-id') || 'jroll_' + Math.random().toString().substr(2, 8)

      // 保存jroll对象
      me.scroller.setAttribute('jroll-id', me.id)
      jrollMap[me.id] = me

      // 默认选项
      me.options = {
        scrollX: false,
        scrollY: true,
        scrollFree: false, // 自由滑动
        minX: null, // 向左滑动的边界值，默认为0
        maxX: null, // 向右滑动的边界值，默认为scroller的宽*-1
        minY: null, // 向下滑动的边界值，默认为0
        maxY: null, // 向上滑动的边界值，默认为scroller的高*-1
        zoom: false, // 使能缩放
        zoomMin: 1, // 最小缩放倍数
        zoomMax: 4, // 最大缩放倍数
        zoomDuration: 400, // 缩放结束后回到限定位置的过渡时间
        bounce: true, // 回弹
        scrollBarX: false, // 开启x滚动条
        scrollBarY: false, // 开启y滚动条
        scrollBarFade: false, // 滚动条使用渐隐模式
        preventDefault: true, // 禁止touchmove默认事件
        momentum: true, // 滑动结束平滑过渡
        autoStyle: true, // 自动为wrapper和scroller添加样式
        autoBlur: true,  // 在滑动时自动将input/textarea失焦
        edgeRelease: true // 边缘释放，滑动到上下边界自动结束，解决手指滑出屏幕没触发touchEnd事件的问题
      }

      for (var i in options) {
        if (i !== 'scroller') {
          me.options[i] = options[i]
        }
      }

      if (me.options.autoStyle) {
        // 将wrapper设为relative
        if (window.getComputedStyle(me.wrapper).position === 'static') {
          me.wrapper.style.position = 'relative'
          me.wrapper.style.top = '0'
          me.wrapper.style.left = '0'
        }
        me.wrapper.style.overflow = 'hidden'
        me.scroller.style.minHeight = '100%'
      }

      me.scroller.style.touchAction = 'none'

      me.x = 0
      me.y = 0

      /**
       * 当前状态，可取值：
       * null
       * preScroll(准备滑动)
       * preZoom(准备缩放)
       * scrollX(横向)
       * scrollY(竖向)
       * scrollFree(各个方向)
       */
      me.s = null
      me.scrollBarX = null // x滚动条
      me.scrollBarY = null // y滚动条

      me._s = {
        startX: 0,
        startY: 0,
        lastX: 0,
        lastY: 0,
        endX: 0,
        endY: 0
      }

      me._z = {
        spacing: 0, // 两指间间距
        scale: 1,
        startScale: 1
      }

      me._event = {
        'scrollStart': [],
        'scroll': [],
        'scrollEnd': [],
        'zoomStart': [],
        'zoom': [],
        'zoomEnd': [],
        'refresh': [],
        'touchEnd': []
      }

      me.refresh(true)
    },

    // 开启
    enable: function () {
      var me = this
      me.scroller.setAttribute('jroll-id', me.id)
      return me
    },

    // 关闭
    disable: function () {
      var me = this
      me.scroller.removeAttribute('jroll-id')
      return me
    },

    // 销毁
    destroy: function () {
      var me = this
      delete jrollMap[me.id]
      delete me.scroller.jroll
      if (me.scrollBarX) {
        me.wrapper.removeChild(me.scrollBarX)
      }
      if (me.scrollBarY) {
        me.wrapper.removeChild(me.scrollBarY)
      }
      me.disable()
      me.scroller.style[utils.tSF] = ''
      me.scroller.style[utils.tSD] = ''
      me.prototype = null
      for (var i in me) {
        if (me.hasOwnProperty(i)) {
          delete me[i]
        }
      }
    },

    // 替换对象
    call: function (target, e) {
      var me = this
      me.scrollTo(me.x, me.y)
      JRoll.jrollActive = target
      if (e) target._start(e)
      return target
    },

    // 刷新JRoll的宽高
    refresh: function (notRefreshEvent) {
      var me = this
      var wrapperStyle = window.getComputedStyle(me.wrapper)
      var scrollerStyle = window.getComputedStyle(me.scroller)
      var paddingX
      var paddingY
      var marginX
      var marginY
      var temp
      var size

      me.wrapperWidth = me.wrapper.clientWidth
      me.wrapperHeight = me.wrapper.clientHeight

      me.scrollerWidth = Math.round(me.scroller.offsetWidth * me._z.scale)
      me.scrollerHeight = Math.round(me.scroller.offsetHeight * me._z.scale)

      // 解决wrapper的padding和scroller的margin造成maxWidth/maxHeight计算错误的问题
      paddingX = parseInt(wrapperStyle['padding-left']) + parseInt(wrapperStyle['padding-right'])
      paddingY = parseInt(wrapperStyle['padding-top']) + parseInt(wrapperStyle['padding-bottom'])
      marginX = parseInt(scrollerStyle['margin-left']) + parseInt(scrollerStyle['margin-right'])
      marginY = parseInt(scrollerStyle['margin-top']) + parseInt(scrollerStyle['margin-bottom'])

      // 最大/最小范围
      me.minScrollX = me.options.minX === null ? 0 : me.options.minX
      me.maxScrollX = me.options.maxX === null ? me.wrapperWidth - me.scrollerWidth - paddingX - marginX : me.options.maxX
      me.minScrollY = me.options.minY === null ? 0 : me.options.minY
      me.maxScrollY = me.options.maxY === null ? me.wrapperHeight - me.scrollerHeight - paddingY - marginY : me.options.maxY

      if (me.minScrollX < 0) {
        me.minScrollX = 0
      }
      if (me.minScrollY < 0) {
        me.minScrollY = 0
      }
      if (me.maxScrollX > 0) {
        me.maxScrollX = 0
      }
      if (me.maxScrollY > 0) {
        me.maxScrollY = 0
      }

      me._s.endX = me.x
      me._s.endY = me.y

      // x滚动条
      if (me.options.scrollBarX) {
        if (!me.scrollBarX) {
          temp = me._createScrollBar('jroll-xbar', 'jroll-xbtn', false)
          me.scrollBarX = temp[0]
          me.scrollBtnX = temp[1]
        }
        me.scrollBarScaleX = me.wrapper.clientWidth / me.scrollerWidth
        size = Math.round(me.scrollBarX.clientWidth * me.scrollBarScaleX)
        me.scrollBtnX.style.width = (size > 8 ? size : 8) + 'px'
        me._runScrollBarX()
      } else if (me.scrollBarX) {
        me.wrapper.removeChild(me.scrollBarX)
        me.scrollBarX = null
      }
      // y滚动条
      if (me.options.scrollBarY) {
        if (!me.scrollBarY) {
          temp = me._createScrollBar('jroll-ybar', 'jroll-ybtn', true)
          me.scrollBarY = temp[0]
          me.scrollBtnY = temp[1]
        }
        me.scrollBarScaleY = me.wrapper.clientHeight / me.scrollerHeight
        size = Math.round(me.scrollBarY.clientHeight * me.scrollBarScaleY)
        me.scrollBtnY.style.height = (size > 8 ? size : 8) + 'px'
        me._runScrollBarY()
      } else if (me.scrollBarY) {
        me.wrapper.removeChild(me.scrollBarY)
        me.scrollBarY = null
      }

      if (!notRefreshEvent) {
        me._execEvent('refresh')
      }

      return me
    },

    scale: function (multiple) {
      var me = this
      var z = parseFloat(multiple)
      if (!isNaN(z)) {
        me.scroller.style[utils.TFO] = '0 0'
        me._z.scale = z
        me.refresh()._scrollTo(me.x, me.y)
        me.scrollTo(me.x, me.y, 400)
      }
      return me
    },

    _wheel: function (e) {
      var me = this
      var y = e.wheelDelta || -(e.detail / 3) * 120 // 兼容火狐
      if (me.options.scrollY || me.options.scrollFree) {
        me.scrollTo(me.x, me._compute(me.y + y, me.minScrollY, me.maxScrollY))
      }
    },

    // 滑动滚动条
    _runScrollBarX: function () {
      var me = this
      var x = Math.round(-1 * me.x * me.scrollBarScaleX)

      me._scrollTo.call({
        scroller: me.scrollBtnX,
        _z: {
          scale: 1
        }
      }, x, 0)
    },
    _runScrollBarY: function () {
      var me = this
      var y = Math.round(-1 * me.y * me.scrollBarScaleY)

      me._scrollTo.call({
        scroller: me.scrollBtnY,
        _z: {
          scale: 1
        }
      }, 0, y)
    },

    // 创建滚动条
    _createScrollBar: function (a, b, isY) {
      var me = this
      var bar
      var btn

      bar = document.createElement('div')
      btn = document.createElement('div')
      bar.className = a
      btn.className = b

      if (this.options.scrollBarX === true || this.options.scrollBarY === true) {
        if (isY) {
          bar.style.cssText = 'position:absolute;top:2px;right:2px;bottom:2px;width:6px;overflow:hidden;border-radius:2px;-webkit-transform: scaleX(.5);transform: scaleX(.5);'
          btn.style.cssText = 'background:rgba(0,0,0,.4);position:absolute;top:0;left:0;right:0;border-radius:2px;'
        } else {
          bar.style.cssText = 'position:absolute;left:2px;bottom:2px;right:2px;height:6px;overflow:hidden;border-radius:2px;-webkit-transform: scaleY(.5);transform: scaleY(.5);'
          btn.style.cssText = 'background:rgba(0,0,0,.4);height:100%;position:absolute;left:0;top:0;bottom:0;border-radius:2px;'
        }
      }

      if (me.options.scrollBarFade) {
        bar.style.opacity = 0
      }

      bar.appendChild(btn)
      me.wrapper.appendChild(bar)

      return [bar, btn]
    },

    // 滚动条渐隐
    _fade: function (bar, time) {
      var me = this
      if (me.fading && time > 0) {
        time = time - 25
        if (time % 100 === 0) bar.style.opacity = time / 1000
      } else {
        return
      }
      rAF(me._fade.bind(me, bar, time))
    },

    on: function (event, callback) {
      var me = this
      switch (event) {
        case 'scrollStart':
          me._event.scrollStart.push(callback)
          break
        case 'scroll':
          me._event.scroll.push(callback)
          break
        case 'scrollEnd':
          me._event.scrollEnd.push(callback)
          break
        case 'zoomStart':
          me._event.zoomStart.push(callback)
          break
        case 'zoom':
          me._event.zoom.push(callback)
          break
        case 'zoomEnd':
          me._event.zoomEnd.push(callback)
          break
        case 'refresh':
          me._event.refresh.push(callback)
          break
        case 'touchEnd':
          me._event.touchEnd.push(callback)
          break
      }
      return me
    },

    _execEvent: function (event, e) {
      var me = this
      var i = me._event[event].length - 1
      for (; i >= 0; i--) {
        me._event[event][i].call(me, e)
      }
    },

    // 计算x,y的值
    _compute: function (val, min, max) {
      var me = this
      if (val > min) {
        if (me.options.bounce && (val > (min + 10))) {
          return Math.round(min + ((val - min) / 4))
        } else {
          return min
        }
      }

      if (val < max) {
        if (me.options.bounce && (val < (max - 10))) {
          return Math.round(max + ((val - max) / 4))
        } else {
          return max
        }
      }

      return val
    },

    _scrollTo: function (x, y) {
      this.scroller.style[utils.TSF] = 'translate(' + x + 'px, ' + y + 'px)' + utils.translateZ + ' scale(' + this._z.scale + ')'
    },

    /**
     * 供用户调用的scrollTo方法
     * x x坐标
     * y y坐标
     * timing 滑动时长，使用css3的transition-duration进行过渡
     * allow  是否允许超出边界，默认为undefined即不允许超出边界
     * system 为true时即是本程序自己调用，默认为undefined即非本程序调用
     */
    scrollTo: function (x, y, timing, allow, callback, system, t) {
      var me = this
      if (!allow) {
        // x
        if (x >= me.minScrollX) {
          me.x = me.minScrollX

          // 滑到最大值时手指继续滑，重置开始、结束位置，优化体验
          if (t) {
            me._s.startX = t[0].pageX
            me._s.endX = me.minScrollX
          }
        } else if (x <= me.maxScrollX) {
          me.x = me.maxScrollX
          if (t) {
            me._s.startX = t[0].pageX
            me._s.endX = me.maxScrollX
          }
        } else {
          me.x = x
        }

        // y
        if (y >= me.minScrollY) {
          me.y = me.minScrollY
          if (t) {
            me._s.startY = t[0].pageY
            me._s.endY = me.minScrollY
          }
        } else if (y <= me.maxScrollY) {
          me.y = me.maxScrollY
          if (t) {
            me._s.startY = t[0].pageY
            me._s.endY = me.maxScrollY
          }
        } else {
          me.y = y
        }
      } else {
        me.x = x
        me.y = y
      }
      if (!system) {
        me._s.endX = me.x
        me._s.endY = me.y
      }
      if (timing) {
        utils.moveTo(me.scroller, me.x, me.y, timing, callback)
      } else {
        me._scrollTo(me.x, me.y)
        if (typeof callback === 'function') {
          callback()
        }
      }

      if (me.scrollBtnX) me._runScrollBarX()
      if (me.scrollBtnY) me._runScrollBarY()

      return me
    },

    scrollToElement: function (selector, timing) {
      var me = this
      var el = typeof selector === 'string' ? me.scroller.querySelector(selector) : selector
      if (el instanceof HTMLElement) {
        var p = utils.computePosition(el, me.scroller)
        var t = utils.computeTranslate(el, me.scroller)
        var x = -(p.left + t.x)
        var y = -(p.top + t.y)
        return me.scrollTo(x, y, timing)
      }
    },

    _endAction: function () {
      var me = this
      me._s.endX = me.x
      me._s.endY = me.y
      me.moving = false

      if (me.options.scrollBarFade && !me.fading) {
        me.fading = true // 标记渐隐滚动条
        if (me.scrollBarX) me._fade(me.scrollBarX, 2000)
        if (me.scrollBarY) me._fade(me.scrollBarY, 2000)
      }
      me._execEvent('scrollEnd')
    },

    _stepBounce: function () {
      var me = this

      me.bouncing = false

      function over () {
        me.scrollTo(me.x, me.y, 300)
      }

      // y方向
      if (me.s === 'scrollY') {
        if (me.directionY === 1) {
          me.scrollTo(me.x, me.minScrollY + 15, 100, true, over)
          me.y = me.minScrollY
        } else {
          me.scrollTo(me.x, me.maxScrollY - 15, 100, true, over)
          me.y = me.maxScrollY
        }

      // x方向
      } else if (me.s === 'scrollX') {
        if (me.directionX === 1) {
          me.scrollTo(me.minScrollX + 15, me.y, 100, true, over)
          me.x = me.minScrollX
        } else {
          me.scrollTo(me.maxScrollX - 15, me.y, 100, true, over)
          me.x = me.maxScrollX
        }
      }
    },

    _x: function (p) {
      var me = this
      var n = me.directionX * p
      if (!isNaN(n)) {
        me.x = me.x + n
        // 达到边界终止惯性，执行回弹
        if (me.x >= me.minScrollX || me.x <= me.maxScrollX) {
          me.moving = false
          if (me.options.bounce) {
            me.bouncing = true // 标记回弹
          }
        }
      }
    },

    _y: function (p) {
      var me = this
      var n = me.directionY * p
      if (!isNaN(n)) {
        me.y = me.y + n
        // 达到边界终止惯性，执行回弹
        if (me.y >= me.minScrollY || me.y <= me.maxScrollY) {
          me.moving = false
          if (me.options.bounce) {
            me.bouncing = true // 标记回弹
          }
        }
      }
    },

    _xy: function (p) {
      var me = this
      var x = Math.round(me.cosX * p)
      var y = Math.round(me.cosY * p)
      if (!isNaN(x) && !isNaN(y)) {
        me.x = me.x + x
        me.y = me.y + y
        // 达到边界终止惯性，执行回弹
        if ((me.x >= me.minScrollX || me.x <= me.maxScrollX) && (me.y >= me.minScrollY || me.y <= me.maxScrollY)) {
          me.moving = false
        }
      }
    },

    _step: function (time) {
      var me = this
      var now = Date.now()
      var t = now - time
      var s = 0

      // 惯性滑动结束，执行回弹
      if (me.bouncing) {
        me._stepBounce()
      }

      // 终止
      if (!me.moving) {
        me._endAction()
        return
      }

      // 防止t为0滑动终止造成卡顿现象
      if (t > 10) {
        me.speed = me.speed - t * (me.speed > 1.2 ? 0.001 : (me.speed > 0.6 ? 0.0008 : 0.0006))
        s = Math.round(me.speed * t)
        if (me.speed <= 0 || s <= 0) {
          me._endAction()
          return
        }
        time = now

        // _do是可变方法，可为_x,_y或_xy，在判断方向时判断为何值，避免在次处进行过多的判断操作
        me._do(s)
        me.scrollTo(me.x, me.y, 0, false, null, true)
        me._execEvent('scroll')
      }

      rAF(me._step.bind(me, time))
    },

    _doScroll: function (d, e) {
      var me = this
      var pageY
      me.distance = d
      if (me.options.bounce) {
        me.x = me._compute(me.x, me.minScrollX, me.maxScrollX)
        me.y = me._compute(me.y, me.minScrollY, me.maxScrollY)
      }
      me.scrollTo(me.x, me.y, 0, me.options.bounce, null, true, (e.touches || [e]))
      me._execEvent('scroll', e)

      // 解决垂直滑动超出屏幕边界时捕捉不到touchend事件无法执行结束方法的问题
      if (e && e.touches && me.options.edgeRelease) {
        pageY = e.touches[0].pageY
        if (pageY <= 10 || pageY >= window.innerHeight - 10) {
          me._end(e)
        }
      }
    },

    // 判断是滑动JRoll还是滑动Textarea（垂直方向）
    _yTextarea: function (e) {
      var me = this
      var target = e.target
      if (target.tagName === 'TEXTAREA' && target.scrollHeight > target.clientHeight &&

        // textarea滑动条在顶部，向上滑动时将滑动权交给textarea
        ((target.scrollTop === 0 && me.directionY === -1) ||

        // textarea滑动条在底部，向下滑动时将滑动权交给textarea
        (target.scrollTop === target.scrollHeight - target.clientHeight && me.directionY === 1))) {
        me._end(e, true)
        return false
      }
      return true
    },

    _start: function (e) {
      var me = this
      var t = e.touches || [e]

      // 判断缩放
      if (me.options.zoom && t.length > 1) {
        me.s = 'preZoom'
        me.scroller.style[utils.TFO] = '0 0'

        var c1 = Math.abs(t[0].pageX - t[1].pageX)
        var c2 = Math.abs(t[0].pageY - t[1].pageY)

        me._z.spacing = Math.sqrt(c1 * c1 + c2 * c2)
        me._z.startScale = me._z.scale

        me.originX = (t[0].pageX - t[1].pageX) / 2 + t[1].pageX -
          (utils.computePosition(me.scroller, document.body).left +
          utils.computeTranslate(me.scroller, document.body).x)

        me.originY = (t[0].pageY - t[1].pageY) / 2 + t[1].pageY -
          (utils.computePosition(me.scroller, document.body).top +
          utils.computeTranslate(me.scroller, document.body).y)

        me._execEvent('zoomStart', e)
        return
      }

      if (me.options.scrollBarFade) {
        me.fading = false // 终止滑动条渐隐
        if (me.scrollBarX) me.scrollBarX.style.opacity = 1
        if (me.scrollBarY) me.scrollBarY.style.opacity = 1
      }

      // 任意方向滑动
      if (me.options.scrollFree) {
        me._do = me._xy
        me.s = 'scrollFree'

      // 允许xy两个方向滑动
      } else if (me.options.scrollX && me.options.scrollY) {
        me.s = 'preScroll'

      // 只允许y
      } else if (!me.options.scrollX && me.options.scrollY) {
        me._do = me._y
        me.s = 'scrollY'

      // 只允许x
      } else if (me.options.scrollX && !me.options.scrollY) {
        me._do = me._x
        me.s = 'scrollX'
      } else {
        me.s = null
        return
      }

      me.distance = 0
      me.lastMoveTime = me.startTime = Date.now()
      me._s.lastX = me.startPositionX = me._s.startX = t[0].pageX
      me._s.lastY = me.startPositionY = me._s.startY = t[0].pageY

      me._execEvent('scrollStart', e)
    },

    _move: function (e) {
      var me = this
      var t = e.touches || [e]
      var now
      var x
      var y
      var dx
      var dy
      var px
      var py
      var sqrtXY
      var directionX = 1
      var directionY = 1

      // 一个很奇怪的问题，在小米5默认浏览器上同时对x,y进行赋值流畅度会降低
      // 因此采取选择性赋值以保证单向运行较好的滑动体验
      if (me.s === 'preScroll' || me.s === 'scrollX' || me.s === 'scrollFree') {
        x = t[0].pageX
      }
      if (me.s === 'preScroll' || me.s === 'scrollY' || me.s === 'scrollFree') {
        y = t[0].pageY
      }

      dx = x - me._s.lastX
      dy = y - me._s.lastY

      me._s.lastX = x
      me._s.lastY = y

      directionX = dx >= 0 ? 1 : -1 // 手指滑动方向，1(向右) | -1(向左)
      directionY = dy >= 0 ? 1 : -1 // 手指滑动方向，1(向下) | -1(向上)

      now = Date.now()

      if (now - me.lastMoveTime > 200 || me.directionX !== directionX || me.directionY !== directionY) {
        me.startTime = now
        me.startPositionX = x
        me.startPositionY = y
        me.directionX = directionX
        me.directionY = directionY
      }

      me.lastMoveTime = now

      px = x - me.startPositionX
      py = y - me.startPositionY

      // 判断滑动方向
      if (me.s === 'preScroll') {
        // 判断为y方向，y方向滑动较常使用，因此优先判断
        if (Math.abs(y - me._s.startY) >= Math.abs(x - me._s.startX)) {
          me._do = me._y
          me.s = 'scrollY'
          return
        }

        // 判断为x方向
        if (Math.abs(y - me._s.startY) < Math.abs(x - me._s.startX)) {
          me._do = me._x
          me.s = 'scrollX'
          return
        }
      }

      // y方向滑动
      if (me.s === 'scrollY') {
        me.y = y - me._s.startY + me._s.endY
        if (me._yTextarea(e)) {
          me._doScroll(py, e)
        }
        return
      }

      // x方向滑动
      if (me.s === 'scrollX') {
        me.x = x - me._s.startX + me._s.endX
        me._doScroll(px, e)
        return
      }

      // 任意方向滑动
      if (me.s === 'scrollFree') {
        me.x = x - me._s.startX + me._s.endX
        me.y = y - me._s.startY + me._s.endY
        sqrtXY = Math.sqrt(px * px + py * py)
        me.cosX = px / sqrtXY
        me.cosY = py / sqrtXY
        me._doScroll(Math.sqrt(px * px + py * py), e)
        return
      }

      // 缩放
      if (me.s === 'preZoom') {
        var c1 = Math.abs(t[0].pageX - t[1].pageX)
        var c2 = Math.abs(t[0].pageY - t[1].pageY)
        var spacing = Math.sqrt(c1 * c1 + c2 * c2)
        var scale = spacing / me._z.spacing * me._z.startScale
        var lastScale

        if (scale < me.options.zoomMin) {
          scale = me.options.zoomMin
        } else if (scale > me.options.zoomMax) {
          scale = me.options.zoomMax
        }

        lastScale = scale / me._z.startScale

        me.x = Math.round(me.originX - me.originX * lastScale + me._s.endX)
        me.y = Math.round(me.originY - me.originY * lastScale + me._s.endY)
        me._z.scale = scale

        me._scrollTo(me.x, me.y)
        me._execEvent('zoom', e)

        return
      }
    },

    _end: function (e, manual) {
      var me = this
      var ex1
      var ex2
      var now = Date.now()
      var s1 = me.s === 'scrollY'
      var s2 = me.s === 'scrollX'
      var s3 = me.s === 'scrollFree'

      // 滑动结束
      if (s1 || s2 || s3) {
        // 禁止第二个手指滑动，只有一个手指时touchend事件的touches.length为0
        // manual参数用于判断是否手动执行_end方法，用于处理带滚动条的texearea
        if (e.touches && e.touches.length && !manual) {
          return
        }

        me._execEvent('touchEnd')
        JRoll.jrollActive = null
        me.duration = now - me.startTime

        ex1 = me.y > me.minScrollY || me.y < me.maxScrollY
        ex2 = me.x > me.minScrollX || me.x < me.maxScrollX

        // 超出边界回弹
        if ((s1 && ex1) || (s2 && ex2) || (s3 && (ex1 || ex2))) {
          me.scrollTo(me.x, me.y, 300)._endAction()

        // 惯性滑动
        } else if (me.options.momentum && me.duration < 200 && me.distance) {
          me.speed = Math.abs(me.distance / me.duration)
          me.speed = me.speed > 2 ? 2 : me.speed
          me.moving = true
          rAF(me._step.bind(me, now))
        } else {
          me._endAction()
        }
        return
      }

      // 缩放结束
      if (me.s === 'preZoom') {
        me._execEvent('touchEnd')
        JRoll.jrollActive = null

        if (me._z.scale > me.options.zoomMax) {
          me._z.scale = me.options.zoomMax
        } else if (me._z.scale < me.options.zoomMin) {
          me._z.scale = me.options.zoomMin
        }

        me.refresh()

        me.scrollTo(me.x, me.y, me.options.zoomDuration)

        me._execEvent('zoomEnd')

        return
      }
    }
  }

  if (typeof module !== 'undefined' && module.exports) {
    module.exports = JRoll
  }
  if (typeof define === 'function') {
    define(function () {
      return JRoll
    })
  }

  window.JRoll = JRoll
})(window, document, Math)
;+function($){
	var defaults={
		inner:false,		//是否添加内框
		bind:'',			//绑定滚动事件、方法的元素
	};
	$.fn.scroller=function(params){
		return this.each(function(){
			if(!this)return;
			var $this = $(this);
			var data = $this.data('scroller');
			var myscroll;
			if (!data) {
				var opt=$.extend(defaults,params);
				//自动添加内框
				if(opt.inner && $(this).children('.kui-content-inner').size()===0){
					var inner=$('<div class="kui-content-inner" />');
					$this.wrapInner('<div class="kui-content-inner" />');
				}
				
				myscroll=new JRoll(this,opt);
				$this.data('scroller',myscroll).css({overflow:'hidden'});
				//绑定方法
				//$.extend($this.constructor.prototype,setScroll);
				//绑定IScroll事件
				var _event=['scrollStart','scroll','scrollEnd','touchEnd','zoomStart','zoomEnd','refresh'];
				$.each(_event,function(i,evt){
					myscroll.on(evt,function(e){
						$this.trigger($.Event(evt));
					});
				});
				//绑定方法
				var _action=['refresh','scrollTo','scrollToElement','disable','enable','destroy','scale','call'];
				$.each(_action,function(i,act){
					$this.constructor.prototype[act]=function(){
						myscroll[act].apply(myscroll,arguments);
					};
				});
				
			}
			return myscroll;
		});
	};
}(Zepto);;!function(win,$){
	"use strict";
	var index=0,tap='click';
	//层入场动画，格式：[CSS默认值，进入效果，关闭效果，动画时间]
	var fxArr={
		left:[{'-webkit-transform':'translateX(-100%)'},{translateX:'0'},{translateX:'-100%'},200],
		right:[{'-webkit-transform':'translateX(100%)'},{translateX:'0'},{translateX:'100%'},200],
		top:[{'-webkit-transform':'translateY(-100%)'},{translateY:'0'},{translateY:'-100%'},300],
		bottom:[{'-webkit-transform':'translatey(100%)'},{translateY:'0'},{translateY:'100%'},300],
		zoom:[{'-webkit-transform':'scale(0.1)',opacity:0.1},{scale:1,opacity:1},{scale:0.1,opacity:0.1},250],
		fade:[{opacity:0,'-webkit-transform':'scale(1.1)'},{opacity:1,scale:1},{opacity:0,scale:1.1},100],
	};
	$.layer=function(option){
		index++;
		var defaults={
			id:'layer-'+index,
			type:'0',
			head:'',
			content:'',
			foot:'',
			skin:'',
			style:{},
			size:[],
			timeout:'',
			anim:'fade',
			shade:true,
			shadeclose:true,
			autoshow:true,
			
			scroller:false,	//是否启用JS滚动
			//事件
			onshow:new Function(),
			onclose:new Function()
		};
		
		//合并用户参数
		var opt=$.extend(defaults,option);
		//检测是否已存在
		if($('#'+opt.id).size()){return false;}
		
		//添加自定义出场效果
		var anim=opt.anim,fx;
		if($.isArray(anim)){
			fx=anim;
			anim='user-fx'+index;
			fxArr[anim]=fx;
		}else{
			fx=fxArr[anim];
		}
		
		/*格式化头部*/
		var head=opt.head||'',hdstyle,hdbtn;
		if($.isPlainObject(opt.head)){
			hdbtn=$.map(($.isArray(opt.head.btn)?opt.head.btn:[]),function(itm,i){
				var icon=itm.icon||'';
				icon=icon.match(/fl|fr/g)?icon:icon+' fl';
				icon=itm.isclose!=(0||false) ? icon+' kui-layer-close':icon;
				
				return $('<a>'+(itm.text||'')+'</a>').addClass(icon).on(tap,(itm.onClick||new Function()));
			})||'';
			head=opt.head.title?'<h1 class="kui-title">'+opt.head.title+'</h1>':'';
			hdstyle=$.isPlainObject(opt.head.style)?opt.head.style:_cssToJson(opt.head.style);
		}
		var $head=head?$('<div class="kui-layer-header">'+head+'</div>').prepend(hdbtn).css(hdstyle).addClass(opt.head.className||''):'';
		
		//side侧栏
		var side=opt.side||'',sidecss={};
		if($.isArray(side)){
			sidecss=setStyle(side[1]);
			side=side[0];
		}
		var $side=side?$('<side class="kui-layer-side">'+side+'</side>').css(sidecss[0]).addClass(sidecss[1]):'';
		
		//内容区
		var content=opt.content||'',constyle={};
		if($.isArray(content)){
			constyle=setStyle(content[1]);
			content=opt.content[0];
		}
		var $content=content?$('<div class="kui-layer-content" />').html(content).css(constyle[0]).addClass(constyle[1]):'';
		
		//底部导航按扭
		var foot=opt.foot||'',ftStyle=[];
		if($.isArray(foot)){
			ftStyle=setStyle(foot[1]);
			foot=foot[0];
		}
		var $foot=foot?$('<div class="kui-layer-footer"></div>').html(foot).css(ftStyle[0]).addClass(ftStyle[1]):'';
		
		//组装
		var type=opt.type,shade=opt.shade;
		
		var $shade=$('<div class="kui-layer-shade'+(opt.shadeclose?' kui-layer-close':'')+'"></div>');
		//shade为CSS对象时，遮罩样式
		if(shade && shade!==true){
			var shd=setStyle(shade);
			$shade.css(shd).addClass(shd);
		}
		var $layer=$('<div class="kui-layer" anim="'+anim+'"></div>').addClass(opt.skin||'').append($side,$head,$content,$foot);
		var $lay=$('<div class="kui-layer-wrap"></div>').addClass('type-'+type).attr({id:opt.id,type:type,'layer-index':index}).append($shade,$layer);
		$lay.appendTo('body');
		
		//属性绑定到层元素中
		$lay.option=opt;
		
		//默认显示层
		if(opt.autoshow)$.layer.open($lay);
		
		//初始化内容JS滚动
		if(opt.scroller){
			//内容滚动对象
			var dft={inner:true,bounce:true,scrollBarY:true,scrollBarFade:true};
			var scropt=$.extend(dft,$.isPlainObject(opt.scroller)?opt.scroller:{});
			var jScr=$content.scroller(scropt);
			//$lay.constructor.prototype.jRoll=jScr;
			//绑定IScroll事件
			/*var _event=['scrollStart','scroll','scrollEnd','touchEnd','zoomStart','zoomEnd','refresh'];
			$.each(_event,function(i,evt){
				jScr.on(evt,function(e){
					$lay.trigger($.Event(evt));
				});
			});*/
		}
		
		//绑定显示方法:obj.open()
		$lay.constructor.prototype.open=function(){
			$.layer.open(this);
		};
		//绑定关闭方法:obj.close()
		$lay.constructor.prototype.close=function(){
			//如果当前元素有路由，则返回一步
			$(this).not('.closeing').hasClass('layer-route')&&history.back();
			$.layer.close(this,opt.close);
		};
		return $lay;
	};
	/*显示窗口*******
	 *index 窗口索引，为all时关闭所有（且不触发关闭事件）
	 *call	当index=all时call可传入窗口类型。否则call传入onshow()事件
	***************/
	$.layer.open=function(index){
		if(index>=0){
			$.layer.open($('[layer-index="'+index+'"]'));
		}else if(typeof(index)=="object"){
			index.size()&&$(index).appendTo('body');//如果元素不存在，则先添加元素
			var $lay=index,$layer=$lay.children('.kui-layer'),$shade=$lay.children('.kui-layer-shade'),$layContent=$layer.children('.kui-layer-content');
			var opt=$lay.option,
			anim=$layer.attr('anim')||'',
			fx=fxArr[anim]||fxArr.fade;
			$lay.css({display:'-webkit-box'});
			if($shade){
				//阻止遮罩层拖动冒泡
				$shade.on('touchmove',function(e){
					if(e && e.preventDefault){
						e.preventDefault();
					}
				});
				//遮罩层动画显示
				$shade.animate({opacity:1},'ease-out',100);
			}
			//添加出场中样式
			$lay.addClass('showing');
			//入场前CSS合并到样式设置中
			var style=$.extend(typeof(opt.style)=='object'?opt.style:_cssToJson(opt.style),fx[0]),
			size=opt.size,
			//入场动画效果
			in_timing=fx[4]||'ease-out';
			//设置size参数
			if(size[0])style.width=size[0];
			if(size[1])style.height=size[1];
			fx[1].opacity=1;
			
			$layer.css(style).animate(fx[1],in_timing,fx[3],function(){
				//删除出场中样式
				$lay.removeClass('showing');
				
				//刷新滚动对象
				/*if(opt.scroller){
					$lay.jRoll.refresh();
				}*/
				//显示后触发onshow事件，返回当前层对象
				opt.onshow($lay);
				//自定义事件
				var e=$.Event('show');
				//触发show事件
				$layer.trigger(e);
				
				//为kui-layer-close类绑定关闭事件
				$lay.find('.kui-layer-close').on(tap,function(){
					$lay.close();
				});
				//为kui-layer-sidebtn类绑定关闭事件
				$lay.find('.kui-layer-sidebtn').on(tap,function(){
					$layer.toggleClass('open-side');
				});
				
				//定时关闭
				if(opt.timeout){
					setTimeout(function(){
						$('#'+$lay.attr('id')).size()>0&&$lay.close();
					},(opt.timeout-1)*1000);
				}
				
			});
			
		}
	};
	/*关闭窗口*******
	 *index 窗口索引，为all时关闭所有（且不触发关闭事件）
	 *call	当index=all时call可传入窗口类型，可关闭同类型所有层。否则call传入onclose()事件
	 *remove	关闭后，是否删除层元素，默认：true
	***************/
	$.layer.close=function(index,call,remove){
		remove=remove===undefined||true;
		if(index=='all'){
			var tp=call?'[type="'+call+'"]':'';
			$('[layer-index]'+tp).remove();
		}else if(index>=0){
			$.layer.close($('[layer-index="'+index+'"]'));
		}else if(typeof(index)=="string"&&$('#'+index).size()){
			$.layer.close($('#'+index));
		}else if(typeof(index)=="object"&&index.size()){
			var $lay=index,$layer=$lay.children('.kui-layer'),$shade=$lay.children('.kui-layer-shade'),opt=$lay.option||{};
			var anim=$layer.attr('anim')||'',fx=fxArr[anim]||fxArr.fade;
			var onclose=call||new Function(),closeanim=fx[2]||{},closeTime=fx[3]||'normal';
			//入场动画效果
			var out_timing=fx[5]||'ease-out';
			//删除自定义动画
			anim.indexOf('user-fx')===0&&delete fxArr[anim];
			//遮罩层关闭动画
			$shade&&$shade.animate({opacity:0},'ease-out',closeTime);
			//关闭层动画（加透明度渐变）
			closeanim.opacity=0;
			//添加正在关闭样式
			$lay.addClass('closeing');
			//关闭效果
			$layer.animate(closeanim,out_timing,closeTime,function(){
				//删除正在关闭样式
				$lay.removeClass('closeing');
				//删除层
				remove&&$lay.remove();
				//自定义事件
				var e=$.Event('close');
				//属性中的关闭回调
				if($.isFunction(opt.onclose))opt.onclose(e);
				//传入的关闭回调
				onclose(e);
				//触发自定义事件
				$layer.trigger(e);
			});
		}
	};
	/*-------------------
	 *遮罩层 elm:容器
	-------------------*/
	$.mask=function(elm){
		var $pg=typeof(elm)=='object'?elm:$(elm||'body'),msk='kui-page-mask';
		var $msk=$pg.children('.'+msk);
		$msk=$msk.size()?$msk:$('<div class="'+msk+'" />');
		$msk.appendTo($pg);
		return $msk;
	};
	/**********************
	 * $.alert 	提示框
	 *content	内容
	 *title		标题:string
	 *button	按扭['确定','取消']:array
	 *callback	点击按扭回调,返回按扭序列:Function
	**********************/
	$.alert=function(content,title,button,callback){
		if($('#kui-alert').size())return;
		var con=$.isPlainObject(content)?content:{content:content};
		var dft={id:'kui-alert',head:'',content:'',foot:'',type:'alert',shadeclose:0},tpr;
		var opt=$.extend(dft,con);
		var tpi={},tpArr=['string','array','function'],tpPr=['title','button','callback'];
		//自动判断类型
		if(title){
			tpr=tpPr[$.inArray($.type(title),tpArr)];
			tpi[tpr]=title;
		}
		if(button){
			tpr=tpPr[$.inArray($.type(button),tpArr)];
			tpi[tpr]=button;
		}
		if(callback){
			tpr=tpPr[$.inArray($.type(callback),tpArr)];
			tpi[tpr]=callback;
		}
		opt.skin+=' kui-alert';
		opt.head=tpi.title||'';				//标题
		callback=tpi.callback||new Function();	//回调
		button=tpi.button||['关闭'];			//按扭
		opt.foot=[$.map(button,function(itm,i){return '<a class="kui-alert-btn '+(i<button.length-1?'kui-1px-r':'')+'" btn="'+i+'">'+itm+'</a>';}).join(''),'kui-1px-t'];
		//窗口页
		var ialert=$.layer(opt);
		//绑定按扭点击事件
		ialert.find('.kui-alert-btn').on(tap,function(){
			var btni=$(this).attr('btn');
			var cbk=callback(btni);
			if(cbk!==false)$.layer.close(ialert);
			//自定义回调事件
			var e=$.Event('callback');
			e.index=btni;
			ialert.trigger(e);
		});
		return ialert;
	};
	
	/********************************
	 * $.actions([btn],option)	操作表
	 *操作表可以让用户从多个可选的操作中选择一个
	 *button 	一维数组数据，数据格式如：[{text:'按扭名',className:'按扭样式名',label:1,onClick:function},{text:'按扭名',onClick:{href:'',target:'',style:''}}]
	 {}为分组符
	 *option	继承layer参数
	 *
	*******************************/
	$.actions=function(button,option){
		button=button||[{text:'关闭',label:1}];
		var sheet='';
		//按扭遍历
		$.each(button,function(i,itm){
			if(itm.text){
				var className=itm.className?' '+itm.className:'',onClick=itm.onClick,
				attr=typeof(onClick)=='object'?' '+$.map(onClick,function(v,k){return k+'='+v;}).join(' '):'';
				var isHide=onClick===false?' unHide':'';
				var label=itm.label?'kui-actions-label':'kui-actions-item';
				sheet+='<a '+attr+' class="'+label+className+isHide+' kui-1px-b" label-index="'+i+'">'+itm.text+'</a>';
				
			}else{
				sheet+='</div><div class="kui-actions-group">';
			}
		});
		//结构层参数
		option=$.extend({content:'',skin:'',type:'actions',anim:'bottom'},option||{});
		option.content='<div class="kui-actions-group">'+sheet+'</div>';
		option.skin+=' kui-actions';
		var actions=$.layer(option);
		actions.find('.kui-actions-item').not('.unHide').on(tap,function(){
			var ti=$(this).attr('label-index');
			var callback=button[ti].onClick;
			typeof(callback)=='function'&&callback();
			$.layer.close(actions);
		});
		return actions;
	};
	
	/**************************
	 * $.toast
	 * option	继承$.layer所有属性
	 * icon		图标
	 * timeout	定时关闭
	**************************/
	$.toast=function(option,icon,timeout){
		option=$.isPlainObject(option)?option:{content:option};
		option.content=(icon?'<i class="toast-icon '+icon+'"></i>':'')+'<div class="toast-msg">'+option.content+'</div>';
		option=$.extend({anim:'fade',type:'toast',timeout:timeout||0},option);
		option.skin='kui-toast '+(option.skin||'');
		return $.layer(option);
	};
	
	/**************************
	 * $.loading 加载
	 *msg		加载展示的文字
	**************************/
	$.loading=function(msg){
		return $.toast({id:'kui-loading',type:'loading',content:msg||'',skin:'kui-loading'},'icon-load');
	};
	
	/****************************
	 * $.tabpage Tab页
	 * tab	内容数组:[{title:'',content:''}]
	 * option.active 数字，默认显示的
	 * option.tabbtn	切换标签按扭组样式，
	***************************/
	$.tabpage=function(tab,option){
		tab=tab||[];
		option=$.extend({head:'',content:'',skin:'',type:'tabpage',active:0,tabbtn:'kui-btn-default',anim:'right'},(option||{}));
		option.type+=' tab';
		option.skin+=' kui-tab';
		option.head={title:'',btn:[{text:'关闭',icon:'fl icon-left'}]};
		$.each(tab,function(i,itm){
			var cur=i==option.active?' active':'';
			option.head.title+='<span class="kui-tab-titm '+option.tabbtn+' '+cur+'">'+itm.title+'</span>';
			option.content+='<div class="kui-tab-content tab_'+i+''+cur+'">'+(itm.content||'')+'</div>';
		});
		option.head.title='<div class="kui-tab-tit kui-btns-group">'+option.head.title+'</div>';
		//option.content='<div class="kui-tab-inner" style="width:'+tab.length+'00%;">'+option.content+'</div>';
		var tabpage=$.layer(option);
		tabpage.on('show',function(){
			$(this).find('.kui-tab-titm').on(tap,function(){
				$(this).addClass('active').siblings().removeClass('active');
				tabpage.find('.kui-tab-content').eq($(this).index()).addClass('active').siblings().removeClass('active');
			});
		});
		return tabpage;
	};
	
	//根据数居返回样式
	var setStyle=function(css){
		var style={},clases='';
		if($.isPlainObject(css)){
			style=css;
		}else if(css.indexOf(':')<0){
			clases=css.match(/^[A-Za-z0-9\-\_\s]+$/g)?css:'';
		}else{
			style=_cssToJson(css);
		}
		return [style,clases];
	};
	//cssToJson
	var _cssToJson=function(css){
		if(!css)return {};
		var json={},icss=css.split(';')||[];
		$.each(icss,function(i,itm){
			var arr=itm.split(':');
			if(arr[0])json[arr[0]]=arr[1];
		});
		return json;
	};
	
}(window,Zepto);