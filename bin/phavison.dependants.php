<?php
	/* --- This function simply populates our return data array and returns it --- */
	function populate_data($erc, $erm, $fc, $fp, $ext, $cd, $silent, $secure){
		if($silent){
			$rd = array(
				'data' => $cd
			);
		}
		if($secure){
			$rd = array(
				'err_code' => $erc,
				'err_msg' => $erm,
				'data' => $cd
			);
		}
		if($silent == false && $secure == false){
			$rd = array(
				'err_code'=> $erc,
				'err_msg'=> $erm,
				'function_called'=> $fc,
				'function_parameters'=> $fp,
				'exec_time'=> $ext,
				'data'=> $cd
			);
		}
		return $rd;
	}
?>
