<?php
	class INI{
		public $fileLocation = "cfg/config.ini";
		
		public function __construct($fileLocationVar = "temp.ini"){
			$this->fileLocation = $fileLocationVar;
		}
		
		public function setFileLocation($value)
		{
			$this->fileLocation = $value;
		}
		
		public function getFileLocation(){
			return $this->fileLocation;
		}
		
		public function getSetting($set, $item) {
			$returnVar = false;
			try {	
				$configArray = array();
				
				if (is_file($this->fileLocation)) {
					$configArray = parse_ini_file($this->fileLocation, true);
					if (isset($configArray[$set][$item])) {
						$returnVar = $configArray[$set][$item];
					} else {
						$returnVar = false;
					}
				}
			
			} catch (Exception $e) {
				$returnVar = false;
			}
			
			return $returnVar;
		}
		public function writeFunctions($array) { 
			$success = true;
			$iniArray = array();
			$iniArray[] = "[FUNCTIONS]";
			foreach($array as $key => $value){
				if(is_array($value))
				{
					foreach($value as $subKey => $subValue){
						$iniArray[] = (is_numeric($subValue) ? $subValue : ''.$subValue.''). " = false";
					}
				}
				else $iniArray[] = (is_numeric($subValue) ? $subValue : ''.$subValue.''). " = false";
			}
			$this->safefilerewrite(implode("\r\n", $iniArray));
	 
			return $success; 
		}
		private function safefilerewrite($dataToSave) {
			if ($openFile = fopen($this->fileLocation, 'w')){
				$startTime = microtime(TRUE);
				do {
					$canWrite = flock($openFile, LOCK_EX);
					if(!$canWrite) usleep(round(rand(0, 100)*1000));
				} while ((!$canWrite) and ((microtime(TRUE) - $startTime) < 5));

				if ($canWrite) {
					fwrite($openFile, $dataToSave);
					flock($openFile, LOCK_UN);
				}
				fclose($openFile);
			}
		}
	}
?>
