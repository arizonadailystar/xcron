<?php

// Pushover CLI notifier for XCron
// Sends a push alert when triggered
// rob@wisnerd.com

class XCronNotifier_pushover
{
  public function CheckValid(&$notifyinfo)
  {
    if (!isset($notifyinfo["app_token"])) return array("success" => false, "error" => "Missing application token", "errorcode" => "missing_app_token");
    if (!isset($notifyinfo["user_key"])) return array("success" => false, "error" => "Missing user key", "errorcode" => "missing_user_key");
    if (isset($notifyinfo["error_limit"]) && !is_int($notifyinfo["error_limit"]))  return array("success" => false, "error" => "Invalid 'error_limit'.  Expected an integer.", "errorcode" => "invalid_error_limit");
    return array("success" => true);
  }

  public function Notify($notifykey, &$notifyinfo, $numerrors, &$sinfo, $schedulekey, $name, $userdisp, $data)
  {
    if (isset($notifyinfo["error_limit"]) && $numerrors > $notifyinfo["error_limit"])  return array("success" => false, "error" => "Notification not sent due to exceeding error limit.", "errorcode" => "limit_exceeded");

    $message = (isset($notifyinfo["prefix"]) ? $notifyinfo["prefix"] : "") . $name . "\n";
    $message .= json_encode($data, JSON_UNESCAPED_SLASHES);

    // append the log file if it exists
    $log = "/var/log/xcron/" . $userdisp . "--" . $name . ".log";
    if (file_exists($log)) {
      $message .= "\n===== LOG FILE =====\n";
      $message .= file_get_contents($log);
    }

    $title = isset($data["success"]) ? ($data["success"] ? "Success" : "Failure") : "Test";

    curl_setopt_array($ch = curl_init(), array(
      CURLOPT_URL => "https://api.pushover.net/1/messages.json",
      CURLOPT_POSTFIELDS => array(
        "token" => $notifyinfo["app_token"],
        "user" => $notifyinfo["user_key"],
        "title" => $title,
        "message" => $message,
      ),
      CURLOPT_SAFE_UPLOAD => true,
      CURLOPT_RETURNTRANSFER => true,
    ));
    curl_exec($ch);
    curl_close($ch);

    return array("success" => true);
  }
}
