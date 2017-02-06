(function($) {
	/* Base64 function to pass the variables. This is not for security. This is to ensure that the data doesn't get messed up */
	var Base64={_keyStr:"ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",encode:function(e){var t="";var n,r,i,s,o,u,a;var f=0;e=Base64._utf8_encode(e);while(f<e.length){n=e.charCodeAt(f++);r=e.charCodeAt(f++);i=e.charCodeAt(f++);s=n>>2;o=(n&3)<<4|r>>4;u=(r&15)<<2|i>>6;a=i&63;if(isNaN(r)){u=a=64}else if(isNaN(i)){a=64}t=t+this._keyStr.charAt(s)+this._keyStr.charAt(o)+this._keyStr.charAt(u)+this._keyStr.charAt(a)}return t},decode:function(e){var t="";var n,r,i;var s,o,u,a;var f=0;e=e.replace(/[^A-Za-z0-9+/=]/g,"");while(f<e.length){s=this._keyStr.indexOf(e.charAt(f++));o=this._keyStr.indexOf(e.charAt(f++));u=this._keyStr.indexOf(e.charAt(f++));a=this._keyStr.indexOf(e.charAt(f++));n=s<<2|o>>4;r=(o&15)<<4|u>>2;i=(u&3)<<6|a;t=t+String.fromCharCode(n);if(u!=64){t=t+String.fromCharCode(r)}if(a!=64){t=t+String.fromCharCode(i)}}t=Base64._utf8_decode(t);return t},_utf8_encode:function(e){e=e.replace(/rn/g,"n");var t="";for(var n=0;n<e.length;n++){var r=e.charCodeAt(n);if(r<128){t+=String.fromCharCode(r)}else if(r>127&&r<2048){t+=String.fromCharCode(r>>6|192);t+=String.fromCharCode(r&63|128)}else{t+=String.fromCharCode(r>>12|224);t+=String.fromCharCode(r>>6&63|128);t+=String.fromCharCode(r&63|128)}}return t},_utf8_decode:function(e){var t="";var n=0;var r=c1=c2=0;while(n<e.length){r=e.charCodeAt(n);if(r<128){t+=String.fromCharCode(r);n++}else if(r>191&&r<224){c2=e.charCodeAt(n+1);t+=String.fromCharCode((r&31)<<6|c2&63);n+=2}else{c2=e.charCodeAt(n+1);c3=e.charCodeAt(n+2);t+=String.fromCharCode((r&15)<<12|(c2&63)<<6|c3&63);n+=3}}return t}}
	
	$.phavison = function(call, options) {
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
			/* This setting (url) is where the phavison file is located! relative to the dependant page */
			url: 'bin/phavison.php',
			variables: false,
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
			if (settings.variables !== false) {
				data_send_array = Base64.encode(JSON.stringify(settings.variables));
			}
			
			// Populate our data that will be sent to Phavison.php
			var data_send = {
				call_to: call,
				parameters: data_send_array
			};

			/* Populate our ajax settings.
			--------- START --------- */
			var ajax_settings_array = {};
			if (settings.ajax_settings !== false) {
				ajax_settings_array = settings.ajax_settings;
			}

			var ajax_settings = $.extend({
				url: settings.url,
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
					alert("PHAVISON-ERROR-" + json_object.err_code + ": " + json_object.err_msg + "\nINFO:\nFunction Called: " + json_object.function_called + "\nParameters Defined: " + json_object.variables + "\nPHP Execution Time: " + json_object.exec_time + " Milliseconds\nTotal Execution Time: " + time_total + " Milliseconds");
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