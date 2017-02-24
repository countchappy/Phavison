(function($) {
	
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
				data_send_array = btoa(JSON.stringify(settings.variables));
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
				if(settings.async_enabled === true){
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