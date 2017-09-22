<?php
	/* --- This function simply populates our return data array and returns it --- */
	function populate_data($errorCode, $errorMessage, $fileCalled, $functionCalled, $functionParameters, $executionTime, $returnData, $silent, $secure){
		$returnArray = array();
		if($silent){
			$returnArray = array(
				'data' => $returnData
			);
		}
		if($secure){
			$returnArray = array(
				'err_code' => $errorCode,
				'err_msg' => $errorMessage,
				'data' => $returnData
			);
		}
		if($silent == false && $secure == false){
			$returnArray = array(
				'err_code'=> $errorCode,
				'err_msg'=> $errorMessage,
				'file_called'=>$fileCalled,
				'function_called'=> $functionCalled,
				'function_parameters'=> $functionParameters,
				'exec_time'=> $executionTime,
				'data'=> $returnData
			);
		}
		return $returnArray;
	}
?>
