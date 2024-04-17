#!/usr/bin/php
<?php

  $xcronErrorCode = 1;
  $xcronErrorMsg = "SSL Certificate Check";
  $xcronHostName = gethostname();
  $xcronFilePath = __FILE__;
  $xcronDateTime = date("Y-m-d H:i:s");

  $cmd = "/usr/bin/curl $argv[1] -vI 2>&1";
  $out = shell_exec($cmd);

  $outArray = explode("\n",$out);
  $capture = "";
  $matchNum = 0;
  foreach($outArray as $line) {
    if ($matchNum > 0) {
      $capture .= $line . "\n";
      $matchNum--;
    }
    $pos = strpos($line, "Server certificate");
    if ($pos > 0) {
      $capture .= $line . "\n";
      $matchNum = 6;
    }
  }

  echo $capture . "\n";

  if ($xcronErrorCode) {
    $xcronReturn = array(
      "success" => false,
      "errorcode" => $xcronErrorCode,
      "error" => $xcronErrorMsg,
      "stats" => array(
        "success" => 0,
        "failure" => 1
      ),
      "hostname" => $xcronHostName,
      "filepath" => $xcronFilePath,
      "triggered" => $xcronDateTime
    );
  } else {
    $xcronReturn = array(
      "success" => true,
      "stats" => array(
        "success" => 1,
        "failure" => 0
      ),
      "hostname" => $xcronHostName,
      "filepath" => $xcronFilePath,
      "triggered" => $xcronDateTime
    );
  }

  echo json_encode($xcronReturn, JSON_UNESCAPED_SLASHES) . "\n";
  exit();

?>
