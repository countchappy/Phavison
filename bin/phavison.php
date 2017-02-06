<?php
	// Get current microtime to calculate execution time
	$time_start = microtime(true);

	// Include the configuration file parser.
	include_once('cfg/parse.php');
	
	// Include the functions needed to run Phavison.
	include_once('phavison.dependants.php');

	// Define key variables for the phavison application.
	$return_data = array();
	$error_code = 0;
	$error_message = "";
	$call_data = null;
	$parameters = null;
	$function = null;

	// Define and populate our phavison settings.
	$ini = new INI("cfg/config.ini");
	
	$settings_phavison_enabled = $ini->getSetting('SETTINGS', 'ENABLE');
	$settings_phavison_secure = $ini->getSetting('SETTINGS', 'ENABLE_SECURE_MODE');
	$settings_phavison_get_enabled = $ini->getSetting('SETTINGS', 'ENABLE_GET');
	$settings_phavison_silent_mode = $ini->getSetting('SETTINGS', 'ENABLE_SILENT_MODE');
	$settings_php_dir = $ini->getSetting('FILES', 'INCLUDE_DIR');
	$settings_non_include_dir = $ini->getSetting('FILES', 'NON_INCLUDE_DIR');
	$settings_dir_perm = $ini->getSetting('FILES', 'DIR_PERM');
	
	// Check if the user has enabled this Application to run.
	if(!$settings_phavison_enabled){
		$error_message = "Your configuration settings are not allowing phavison to run";
		$error_code = "I-Err_1";
		$call_data = null;
	}
	
	/* Check if the Phavison's include and non include PHP directories exists, if not, make the directories.
	-------- START FOLDER PROCESSING -------- */
	if(!file_exists($settings_php_dir)){
		if(!mkdir($settings_php_dir, $settings_php_dir_perm, true)){
			$error_message = "one of your PHP directories has failed to be created.";
			$error_code = "I-Err_2";
			$call_data = null;
			$settings_phavison_enabled = false;
		}
	}
	if(!file_exists($settings_non_include_dir)){
		if(!mkdir($settings_non_include_dir, $settings_dir_perm, true)) {
			$error_message = "one of your PHP directories has failed to be created.";
			$error_code = "I-Err_2";
			$call_data = null;
			$settings_phavison_enabled = false;
		}
	}
	/* -------- END FOLDER PROCESSING -------- */

	
	/* -------- Include all of the php files specified in the $settings_php_dir directory -------- */
	foreach (glob($settings_php_dir . "*.php") as $filename) { 
		include_once($filename);
	}
	
	// Check if enabled and continue!
	if($settings_phavison_enabled){
		
		// Make sure that the post (or get if enabled) method of 'call_to' are defined.
		
		if (isset($_POST['call_to'])) {	
		
			// Get the name of the function from the post data
			$function = $_POST['call_to'];
			
			if (isset($_POST['parameters'])) {
				$parameters = $_POST['parameters'];
			}
			
			run_function();
			
		} else if(isset($_GET['call_to'])){
			if($settings_phavison_get_enabled){
				// Get the name of the function from the get data
				$function = $_GET['call_to'];
				
				if(isset($_GET['parameters'])){
					$parameters = $_GET['parameters'];
				}
				
				run_function();
			} else {
				$error_message = "a GET call was made to Phavison. GET requests are not enabled in the settings. The script will discontinue with the request.";
				$error_code = "I-Err_3";
				$call_data = null;
			}
		} else {
			$error_code = "R-Err_1";
			$error_message = "No 'call_to' function was defined. Please specify a function and try again.";
		}
	}
	
	function run_function(){
		
		// We first need to bring in our dependant globals.
		global $function, $parameters, $call_data, $error_code, $error_message, $settings_php_dir;
		
		// Here we check if the function exists then actually call the function.
		if(function_exists($function)){
			$call_data = $function(json_decode(base64_decode($parameters), true));
		} else {
			$error_code = "R-Err_2";
			$error_message = "A call to the function '$function' was made. That function does not exist or is not within a php file under the home directory located at '".$settings_php_dir."'";
		}
	}
	
	// Calculate script running time (for debugging if needed).
	$execution_time = (microtime(true) - $time_start) * 1000;
	// Run the function and return the json object to jquery.phavison(.min).js
	$return_data = populate_data($error_code, $error_message, $function, $parameters, $execution_time, $call_data, $settings_phavison_silent_mode, $settings_phavison_secure);
	echo json_encode($return_data);
?>
