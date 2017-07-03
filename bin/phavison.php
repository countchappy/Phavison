<?php
	// Get current microtime to calculate execution time
	$time_start = microtime(true);

	// Include the configuration file parser.
	include_once('cfg/parse.php');
	
	// Include the functions needed to run Phavison.
	include_once('phavison.dependants.php');

	// Define key variables for the phavison application.
	$returnData = array();
	$errorCode = 0;
	$errorMessage = "";
	$callData = null;
	$parameters = null;
	$function = null;

	// Define and populate our phavison settings.
	$ini = new INI("cfg/config.ini");
	
	$settingsPhavisonEnabled = $ini->getSetting('SETTINGS', 'ENABLE');
	if($settingsPhavisonEnabled){
		$settingsPhavisonGetEnabled = $ini->getSetting('SETTINGS', 'ENABLE_GET');
		$settingsPhpDir = $ini->getSetting('FILES', 'INCLUDE_DIR');
		$settingsNonIncludeDir = $ini->getSetting('FILES', 'NON_INCLUDE_DIR');
		$settingsDirPerm = $ini->getSetting('FILES', 'DIR_PERM');
		$settingsWhitelistFunctions = $ini->getSetting('FUNCTIONS', 'WHITELIST_ENFORCED');
		$settingsWhitelistSetup = $ini->getSetting('FUNCTIONS', 'WHITELIST_SETUP');
		$settingsWhitelistFile = $ini->getSetting('FUNCTIONS', 'WHITELIST_FILE');
	}
	$settingsPhavisonSecure = $ini->getSetting('SETTINGS', 'ENABLE_SECURE_MODE');
	$settingsPhavisonSilentMode = $ini->getSetting('SETTINGS', 'ENABLE_SILENT_MODE');
	
	// Check if the user has enabled this Application to run.
	if(!$settingsPhavisonEnabled){
		$errorMessage = "Your configuration settings are not allowing phavison to run";
		$errorCode = "I-Err_1";
		$callData = null;
	}
	
	// Check if enabled and continue!
	if($settingsPhavisonEnabled){
		/* Check if the Phavison's include and non include PHP directories exists, if not, make the directories.
		-------- START FOLDER PROCESSING -------- */
		if(!file_exists($settingsPhpDir)){
			if(!mkdir($settingsPhpDir, $settingsPhpDir_perm, true)){
				$errorMessage = "one of your PHP directories has failed to be created.";
				$errorCode = "I-Err_2";
				$callData = null;
				$settingsPhavisonEnabled = false;
			}
		}
		if(!file_exists($settingsNonIncludeDir)){
			if(!mkdir($settingsNonIncludeDir, $settingsDirPerm, true)) {
				$errorMessage = "one of your PHP directories has failed to be created.";
				$errorCode = "I-Err_2";
				$callData = null;
				$settingsPhavisonEnabled = false;
			}
		}
		/* -------- END FOLDER PROCESSING -------- */
		
		/* -------- Include all of the php files specified in the $settingsPhpDir directory -------- */
		
		// - If the setup flag is marked. Perform setup.
		if($settingsWhitelistSetup){
			
			// Define our regular expression to search for functions in the PHP FILES
			$functionRegex = '/function[\s\n]+(\S+)[\s\n]*\(/';
			$rawDataArray = array();
			$mainFunctionArray = array();
			$iniDataArray = array();
			$arrayIndex = 0;
			
			// For every PHP file in the include directory, check for functions
			foreach (glob($settingsPhpDir . "*.php") as $filename) {
				$fileContents = file_get_contents($filename);
				
				// Flag all matches and push them to the $rawDataArray variable
				preg_match_all( $functionRegex , $fileContents , $rawDataArray );
				
				// If the $rawDataArray is filled (meaning function names were found) continue the process
				if(count($rawDataArray) > 1){
					$mainFunctionArray = $rawDataArray[1];
					
					// For each function name, pass it to the INI array
					foreach ($mainFunctionArray as $functionName) {
						$iniDataArray[strtoupper(basename($filename, ".php"))][$functionName] = "false";
					}
				}
			}
			$ini->writeIniFile($iniDataArray, "cfg/" . $settingsWhitelistFile, true);
		}
		foreach (glob($settingsPhpDir . "*.php") as $filename) { 
			include_once($filename);
		}
		
		// Make sure that the post (or get if enabled) method of 'call_to' are defined.
		
		if (isset($_POST['call_to'])) {	
		
			// Get the name of the function from the post data
			$function = $_POST['call_to'];
			
			if (isset($_POST['parameters'])) {
				$parameters = $_POST['parameters'];
			}
			
			run_function();
			
		} else if(isset($_GET['call_to'])){
			if($settingsPhavisonGetEnabled){
				// Get the name of the function from the get data
				$function = $_GET['call_to'];
				
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
			$errorMessage = "No 'call_to' function was defined. Please specify a function and try again.";
		}
	}
	
	function run_function(){
		
		// We first need to bring in our dependant globals.
		global $function, $parameters, $callData, $errorCode, $errorMessage, $settingsPhpDir, $settingsWhitelistFile, $settingsWhitelistFunctions;
		
		// Here we check if the function exists then actually call the function.
		$fini = new INI("cfg/" . $settingsWhitelistFile);
		$isWhitelisted = $fini->getSetting(null, $function, false);
		
		if($settingsWhitelistFunctions){
			if($isWhitelisted && function_exists($function)){
				$callData = $function(json_decode(base64_decode($parameters), true));
			} else {
				$errorCode = "R-Err_3";
				$errorMessage = "A call to the function '$function' was made. That function is not callable as specified by the whitelist, please update the whitelist and try again";
			}
		}
		if(!$settingsWhitelistFunctions){
			if(function_exists($function)){
				$callData = $function(json_decode(base64_decode($parameters), true));
			} else {
				$errorCode = "R-Err_2";
				$errorMessage = "A call to the function '$function' was made. That function does not exist or is not within a php file under the home directory located at '".$settingsPhpDir."'";
			}
		}
	}
	
	// Calculate script running time (for debugging if needed).
	$executionTime = (microtime(true) - $time_start) * 1000;
	// Run the function and return the json object to jquery.phavison(.min).js
	$returnData = populate_data($errorCode, $errorMessage, $function, $parameters, $executionTime, $callData, $settingsPhavisonSilentMode, $settingsPhavisonSecure);
	echo json_encode($returnData);
?>
