<?php
	class INI{
		public $fileLocation = "cfg/config.ini";
		
		public function __construct($fileLocationVar){
			$this->fileLocation = $fileLocationVar;
			define('yes', true);
			define('no', false);
		}
		
		public function getFileLocale(){
			return $this->fileLocation;
		}
		
		public function getSetting($set, $item) {
			$returnVar = null;
			try {	
				$configArray = array();
				
				if (is_file($this->fileLocation)) {
					$configArray = parse_ini_file($this->fileLocation, true);
					if ($configArray[$set][$item] !== 'undefined' && $configArray !== null) {
						$returnVar = $configArray[$set][$item];
					} else {
						$returnVar = "C-Err_2";
					}
				}
			
			} catch (Exception $e) {
				$returnVar = "Something went wrong in the config parser!";
			}
			
			return $returnVar;
		}
	}
?>
