(function($) {
	$.phavison = function(call, options) {
		// --------------- Define BASE64 Function ---------------
		var Base64={_keyStr:"ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",encode:function(r){var t,e,o,a,h,n,c,d="",C=0;for(r=Base64._utf8_encode(r);C<r.length;)t=r.charCodeAt(C++),e=r.charCodeAt(C++),o=r.charCodeAt(C++),a=t>>2,h=(3&t)<<4|e>>4,n=(15&e)<<2|o>>6,c=63&o,isNaN(e)?n=c=64:isNaN(o)&&(c=64),d=d+this._keyStr.charAt(a)+this._keyStr.charAt(h)+this._keyStr.charAt(n)+this._keyStr.charAt(c);return d},decode:function(r){var t,e,o,a,h,n,c,d="",C=0;for(r=r.replace(/[^A-Za-z0-9\+\/\=]/g,"");C<r.length;)a=this._keyStr.indexOf(r.charAt(C++)),h=this._keyStr.indexOf(r.charAt(C++)),n=this._keyStr.indexOf(r.charAt(C++)),c=this._keyStr.indexOf(r.charAt(C++)),t=a<<2|h>>4,e=(15&h)<<4|n>>2,o=(3&n)<<6|c,d+=String.fromCharCode(t),64!=n&&(d+=String.fromCharCode(e)),64!=c&&(d+=String.fromCharCode(o));return d=Base64._utf8_decode(d)},_utf8_encode:function(r){r=r.replace(/\r\n/g,"\n");for(var t="",e=0;e<r.length;e++){var o=r.charCodeAt(e);128>o?t+=String.fromCharCode(o):o>127&&2048>o?(t+=String.fromCharCode(o>>6|192),t+=String.fromCharCode(63&o|128)):(t+=String.fromCharCode(o>>12|224),t+=String.fromCharCode(o>>6&63|128),t+=String.fromCharCode(63&o|128))}return t},_utf8_decode:function(r){for(var t="",e=0,o=c1=c2=0;e<r.length;)o=r.charCodeAt(e),128>o?(t+=String.fromCharCode(o),e++):o>191&&224>o?(c2=r.charCodeAt(e+1),t+=String.fromCharCode((31&o)<<6|63&c2),e+=2):(c2=r.charCodeAt(e+1),c3=r.charCodeAt(e+2),t+=String.fromCharCode((15&o)<<12|(63&c2)<<6|63&c3),e+=3);return t}};
		// ------------------------------------------------------
		
		// If options is undefined make an empty object for it
		if (options === undefined) {
			options = {};
		}
		
		// Get execution time.
		var time_start = new Date().getMilliseconds();

		// Defining our variables.
		var return_data;

		// Setting our defaults if they were not passed.
		var settings = $.extend({
			/* This setting (phavison_url) is where the phavison file is located! relative to the dependant page */
			phavison_url: 'bin/phavison.php',
			function_parameters: false,
			async_enabled: true,
			async_function: 'phavisonCallback',
			debug_error: false,
			debug_notify: false,
			ajax_settings: false,
			get_enabled: false
		}, options);

		// Here we check if the function to be called was defined or not!
		if (call !== null) {
			
			// Set the mode of data transfer.
			var method_type = "POST";
			if (settings.get_enabled !== false) {
				method_type = "GET";
			}
			
			// Populate the function's parameters if they are defined.
			var data_send_array = null;
			if (settings.function_parameters !== false) {
				data_send_array = Base64.encode(JSON.stringify(settings.function_parameters));
			}
			
			// Populate our data that will be sent to Phavison.php
			var data_send = {
				call_to: call,
				para: data_send_array
			};

			/* Populate our ajax settings.
			--------- START --------- */
			var ajax_settings_array = {};
			if (settings.ajax_settings !== false) {
				ajax_settings_array = settings.ajax_settings;
			}

			var ajax_settings = $.extend({
				url: settings.phavison_url,
				method: method_type,
				async: settings.async_enabled,
				data: data_send
			}, ajax_settings_array);
			// --------- END ---------
			
			// Make the ajax request.
			$.ajax(ajax_settings).done(function(data) {
				
				// The following code will be run on a succesful AJAX request.
				
				return_data = data;
				
				// Mainly for debugging but if the call has selected 'notify' to be true, the script will alert with the results, error or not.
				if (settings.debug_notify) {
					alert(JSON.stringify(return_data).split("\\").join(""));
				}
				
				// Similar to the above, however this will only alert the errors when they happen.
				var json_object = jQuery.parseJSON(return_data);
				
				if (settings.debug_error && json_object.err_code !== 0) {
					var time_end = new Date().getMilliseconds();
					var time_total = time_end - time_start;
					if (time_total < 0) {
						time_total = time_total + 1;
					}
					alert("PHAVISON-ERROR-" + json_object.err_code + ": " + json_object.err_msg + "\nINFO:\nFunction Called: " + json_object.function_called + "\nParameters Defined: " + json_object.function_parameters + "\nPHP Execution Time: " + json_object.exec_time + " Milliseconds\nTotal Execution Time: " + time_total + " Milliseconds");
				}
				
				// This will start the async call function (If defined).
				if(settings.async_enabled == true){
					window[settings.async_function](json_object);
				}
				
				
			}).fail(function() {
				// The code below will only execute if the ajax call has failed!
				
				return_data = {
					// If the AJAX fails this will ALWAYS be sent back.
					error: "The Phavison AJAX call has failed please verify your phavison settings and make the call again."
				};
				
				if(settings.debug_error){
					// If the AJAX call fails and the user has defined 'debug_error' to be true, then it will alert the error.
					alert("The Phavison AJAX call has failed please verify your phavison settings and make the call again.");
				}
			});
		} else {
			// If no function was defined, execute the following.
			return_data = {
				error: "No PHP function was given. Please try again!"
			};
		}
		return return_data;
	};
}(jQuery));