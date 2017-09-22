<?php
	// Get current microtime to calculate execution time
	$time_start = microtime(true);
	
	// Include the functions needed to run Phavison.
	include_once('phavison.dependants.php');

	// Define key variables for the phavison application.
	$returnData = array();
	$errorCode = 0;
	$errorMessage = "";
	$callData = null;
	$parameters = null;
	$functionToCall = null;
	$fileToCall = null;
	
	// Get our phavison configuration
	
	$configuration = require("settings" . DIRECTORY_SEPARATOR . "config.php");
	
	/* Check if the Phavison's include and non include PHP directories exists, if not, make the directories.
	-------- START FOLDER PROCESSING -------- */
	if(!file_exists($configuration['FILES']['INCLUDE_DIR'])){
		if(!mkdir($configuration['FILES']['INCLUDE_DIR'], $configuration['FILES']['DIR_PERM'], true)){
			$errorMessage = "one of your PHP directories has failed to be created.";
			$errorCode = "I-Err_2";
			$callData = null;
			$configuration['GLOBALS']['ENABLE'] = false;
		}
	}
	if(!file_exists($configuration['FILES']['NON_INCLUDE_DIR'])){
		if(!mkdir($configuration['FILES']['NON_INCLUDE_DIR'], $configuration['FILES']['DIR_PERM'], true)) {
			$errorMessage = "one of your PHP directories has failed to be created.";
			$errorCode = "I-Err_2";
			$callData = null;
			$configuration['GLOBALS']['ENABLE'] = false;
		}
	}
	/* -------- END FOLDER PROCESSING -------- */
	
	if($configuration['GLOBALS']['ENABLE']){
		/* -------- Include all of the php files specified in the $configuration['FILES']['INCLUDE_DIR'] directory -------- */
		
		// - If the setup flag is marked. Perform setup.
		if($configuration['WHITELIST']['SETUP']){
			
			// Define our regular expression to search for functions in the PHP FILES
			$functionRegex = '/function[\s\n]+(\S+)[\s\n]*\(/';
			$rawDataArray = array();
			$mainFunctionArray = array();
			$cfgDataArray = array();
			$arrayIndex = 0;
			
			// For every PHP file in the include directory, check for functions
			foreach (glob($configuration['FILES']['INCLUDE_DIR'] . DIRECTORY_SEPARATOR . "*.php") as $filename) {
				$fileContents = file_get_contents($filename);
				
				// Flag all matches and push them to the $rawDataArray variable
				preg_match_all( $functionRegex , $fileContents , $rawDataArray );
				
				// If the $rawDataArray is filled (meaning function names were found) continue the process
				if(count($rawDataArray) > 1){
					$mainFunctionArray = $rawDataArray[1];
					
					// For each function name, pass it to the CFG array
					foreach ($mainFunctionArray as $functionName) {
						$cfgDataArray[basename($filename)][$functionName] = array(
							'enabled'=>false
						);
					}
				}
			}
			file_put_contents('settings' . DIRECTORY_SEPARATOR . $configuration['WHITELIST']['FILE'], "<?php\nreturn " . var_export($cfgDataArray, true) . ";\n?>");
			
			$configuration['WHITELIST']['SETUP'] = false;
			file_put_contents('settings' . DIRECTORY_SEPARATOR . 'config.php', "<?php\nreturn " . var_export($configuration, true) . ";\n?>");
		}
		
		foreach (glob($configuration['FILES']['INCLUDE_DIR'] . "*.php") as $filename) { 
			include_once($filename);
		}
		
		// Make sure that the post (or get if enabled) method of 'function' and 'file' are defined.
		
		if (isset($_POST['functionToCall']) && isset($_POST['fileToCall'])) {	
		
			// Get the name of the function from the post data
			$functionToCall = $_POST['functionToCall'];
			// Get the name of the file from the post data
			$fileToCall = $_POST['fileToCall'];
			
			if (isset($_POST['parameters'])) {
				$parameters = $_POST['parameters'];
			}
			
			run_function();
		} else if(isset($_GET['functionToCall']) && isset($_GET['fileToCall'])){
			if($configuration['GLOBALS']['ENABLE_GET']){
				// Get the name of the function from the get data
				$functionToCall = $_GET['functionToCall'];
				// Get the name of the file from the get data
				$fileToCall = $_GET['fileToCall'];
				
				if(isset($_GET['parameters'])){
					$parameters = $_GET['parameters'];
				}
				
				run_function();
			} else {
				$errorMessage = "a GET call was made to Phavison. GET requests are not enabled in the settings. The script will discontinue with the request.";
				$errorCode = "I-Err_3";
				$callData = null;
			}
		} else {
			$errorCode = "R-Err_1";
			$errorMessage = "No function or file was defined. Please specify the file and the corresponding function and try again.";
		}
	}
	
	// Check if the user has enabled this Application to run.
	if(!$configuration['GLOBALS']['ENABLE']){
		$errorMessage = "Your configuration settings are not allowing phavison to run";
		$errorCode = "I-Err_1";
		$callData = null;
	}
	
	function run_function(){
		
		// We first need to bring in our dependant globals.
		global $fileToCall, $functionToCall, $parameters, $callData, $errorCode, $errorMessage, $configuration;
		
		// Here we check if the function exists then actually call the function.
		
		if($configuration['WHITELIST']['ENABLE']){
			$whitelist = require('settings' . DIRECTORY_SEPARATOR . $configuration['WHITELIST']['FILE']);
			
			$isWhitelisted = $whitelist[$fileToCall][$functionToCall]['enabled'];
			
			if($isWhitelisted && function_exists($functionToCall)){
				$callData = $functionToCall(json_decode(base64_decode($parameters), true));
			} else {
				$errorCode = "R-Err_3";
				$errorMessage = "A call to the function '$functionToCall' was made. That function is not callable as specified by the whitelist, please update the whitelist and try again";
			}
		}
		if(!$configuration['WHITELIST']['ENABLE']){
			if(function_exists($functionToCall)){
				$callData = $functionToCall(json_decode(base64_decode($parameters), true));
			} else {
				$errorCode = "R-Err_2";
				$errorMessage = "A call to the function '$functionToCall' was made. That function does not exist or is not within a php file under the home directory located at '".$configuration['FILES']['INCLUDE_DIR']."'";
			}
		}
	}
	
	// Calculate script running time (for debugging if needed).
	$executionTime = (microtime(true) - $time_start) * 1000;
	// Run the function and return the json object to jquery.phavison(.min).js
	$returnData = populate_data($errorCode, $errorMessage, $fileToCall, $functionToCall, $parameters, $executionTime, $callData, $configuration['GLOBALS']['SILENT_MODE'], $configuration['GLOBALS']['SECURE_MODE']);
	echo json_encode($returnData);
?>
