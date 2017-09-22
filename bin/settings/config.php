<?php
	return array (
		'FILES' => array (
			'INCLUDE_DIR' => 'include_php/',
			'NON_INCLUDE_DIR' => 'non_include_php/',
			'DIR_PERM' => '0755',
		),
		'GLOBALS' => array (
			'ENABLE' => true,
			'SILENT_MODE' => true,
			'SECURE_MODE' => false,
			'ENABLE_GET' => false,
		),
		'WHITELIST' => array (
			'ENABLE' => true,
			'SETUP' => true,
			'FILE' => 'functions.php',
		),
	);
?>