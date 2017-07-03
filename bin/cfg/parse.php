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
		
		public function getSetting($set = null, $item = null, $processSections = true) {
			$returnVar = false;
			try {	
				$configArray = array();
				
				if (is_file($this->fileLocation)) {
					$configArray = parse_ini_file($this->fileLocation, $processSections);
					if($processSections){
						if (isset($configArray[$set][$item])) {
							$returnVar = $configArray[$set][$item];
						}
					} else {
						if (isset($configArray[$item])) {
							$returnVar = $configArray[$item];
						}
					}
				}
			
			} catch (Exception $e) {
				$returnVar = false;
			}
			
			return $returnVar;
		}
		
		function writeIniFile($iniArray, $path = "cfg/functions.ini", $hasSections=FALSE) { 
			$this->fileLocation = $path;
			$content = ""; 
			if ($hasSections) { 
				foreach ($iniArray as $key=>$elem) { 
					$content .= "[".$key."]\n"; 
					foreach ($elem as $key2=>$elem2) { 
						if(is_array($elem2)) 
						{ 
							for($i=0;$i<count($elem2);$i++) 
							{ 
								$content .= $key2."[] = ".$elem2[$i]."\n"; 
							} 
						} 
						else if($elem2=="") $content .= $key2." = \n"; 
						else $content .= $key2." = ".$elem2."\n"; 
					} 
				} 
			} 
			else { 
				foreach ($iniArray as $key=>$elem) { 
					if(is_array($elem)) 
					{ 
						for($i=0;$i<count($elem);$i++) 
						{ 
							$content .= $key."[] = ".$elem[$i]."\n"; 
						} 
					} 
					else if($elem=="") $content .= $key." = \n"; 
					else $content .= $key." = ".$elem."\n"; 
				} 
			} 

			
			
			if (!$handle = fopen($this->fileLocation, 'w')) { 
				return false; 
			}

			$success = fwrite($handle, $content);
			fclose($handle); 

			return $success; 
		}
	}
?>
