var Phavison = function(fileToBeCalled, functionToBeCalled, options) {
	// If options is undefined make an empty object for it
	if (options === undefined) {
		options = {};
	}

	// Defining our variables.
	var returnData;

	// Setting our defaults if they were not passed.
	var settings = $.extend({
		/* This setting (url) is where the phavison file is located! relative to the dependant page */
		url: 'bin/phavison.php',
		variables: false,
		async: true,
		callback: 'phavisonCallback',
		debug: false,
		ajaxSettings: false,
		get: false
	}, options);
	
	// Here we check if the function to be called was defined or not!
	if (functionToBeCalled !== null && fileToBeCalled !== null) {
		
		// Set the mode of data transfer.
		var ajaxMethodType = "POST";
		if (settings.get !== false) {
			ajaxMethodType = "GET";
		}
		
		// Populate the function's parameters if they are defined.
		var parametersEncoded = null;
		if (settings.variables !== false) {
			parametersEncoded = btoa(JSON.stringify(settings.variables));
		}
		
		// Populate our data that will be sent to Phavison.php
		var ajaxData = {
			functionToCall: functionToBeCalled,
			fileToCall: fileToBeCalled,
			parameters: parametersEncoded
		};

		/* Populate our ajax settings.
		--------- START --------- */
		var ajaxSettings = {};
		if (settings.ajaxSettings !== false) {
			ajaxSettings = settings.ajaxSettings;
		}

		var ajaxSettings = $.extend({
			url: settings.url,
			method: ajaxMethodType,
			async: settings.async,
			data: ajaxData
		}, ajaxSettings);
		// --------- END ---------
		
		// Make the ajax request.
		$.ajax(ajaxSettings).done(function(returnData) {
			
			// The following code will be run on a succesful AJAX request.
			
			var returnedJSON = jQuery.parseJSON(returnData);
			
			if (settings.debug) {
				console.log("Phavison Debug:\n",
				"Error Code: " + returnedJSON.errorCode + "\n",
				"Error Message: " + returnedJSON.errorMessage + "\n",
				"File Called: " + returnedJSON.fileCalled + "\n",
				"Function Called: " + returnedJSON.functionCalled + "\n",
				"Variables Defined: " + settings.variables + "\n",
				"Data Returned:\n" + returnedJSON.dataReturned);
			}
			
			// This will start the async call function (If defined).
			if(settings.async === true){
				if(typeof(window[settings.callback])==="function"){
					window[settings.callback](returnedJSON);
				}
			}
			
		}).fail(function() {
			// The code below will only execute if the ajax call has failed!
			
			if(settings.debug){
				// If the AJAX call fails and the user has defined 'debug' to be true, then it will alert the error.
				console.log("PHAVISON: AJAX Call Failed!");
			}
		});
	} else {
		// If no function was defined, execute the following.
		returnData = {
			error: "No PHP function was given. Please try again!"
		};
	}
	return returnData;
};