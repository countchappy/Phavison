<?php
	class INI{
		public $file_locale = "cfg/config.ini";
		
		public function __construct($fl_par){
			$this->file_locale = $fl_par;
			define('yes', true);
			define('no', false);
		}
		
		public function getFileLocale(){
			return $this->file_locale;
		}
		
		public function getSetting($set, $item) {
			$return_var = null;
			try {	
				$cfg_ini_arr = array();
				
				if (is_file($this->file_locale)) {
					$cfg_ini_arr = parse_ini_file($this->file_locale, true);
					if ($cfg_ini_arr[$set][$item] !== 'undefined' && $cfg_ini_arr !== null) {
						$return_var = $cfg_ini_arr[$set][$item];
					} else {
						$return_var = "C-Err_2";
					}
				}
			
			} catch (Exception $e) {
				$return_var = "Something went wrong in the config parser!";
			}
			
			return $return_var;
	}
	}
?>
