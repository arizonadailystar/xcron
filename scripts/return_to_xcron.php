#!/usr/bin/php
<?php

	$xcronErrorCode = 0;
	$xcronErrorMsg = "An error has occurred";
  $xcronHostName = gethostname();
  $xcronFilePath = __FILE__;

	if ($xcronErrorCode) {
		$xcronReturn = array(
			"success" => false,
			"errorcode" => $xcronErrorCode,
			"error" => $xcronErrorMsg,
			"update_schedule" => time() + 120,
			"stats" => array(
				"success" => 0,
				"failure" => 1
      ),
      "hostname" => $xcronHostName,
      "filepath" => $xcronFilePath
		);
		echo json_encode($xcronReturn, JSON_UNESCAPED_SLASHES) . "\n";
		exit();
	}

  $xcronReturn = array(
    "success" => true,
    "update_schedule" => time() + 120,
		"stats" => array(
      "success" => 1,
      "failure" => 0
    ),
    "hostname" => $xcronHostName,
    "filepath" => $xcronFilePath
);
  echo json_encode($xcronReturn, JSON_UNESCAPED_SLASHES) . "\n";
  exit();

?>
