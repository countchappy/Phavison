<?php
	/* --- This function simply populates our return data array and returns it --- */
	function populate_data($erc, $erm, $fc, $fp, $ext, $cd, $sm){
		if($sm){
			$rd = array(
				'err_code' => $erc,
				'err_msg' => $erm,
				'data' => $cd
			);
		} else {
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
