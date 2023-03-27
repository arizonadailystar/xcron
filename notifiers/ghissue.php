<?php
// GitHub CLI notifier for XCron
// Creates GH issues when triggered
// rob@wisnerd.com

	class XCronNotifier_ghissue
	{
		public function CheckValid(&$notifyinfo)
		{
			if (!isset($notifyinfo["repo"])) return array("success" => false, "error" => "Missing repository", "errorcode" => "missing_repository");
			if (!isset($notifyinfo["code_format"]))  return array("success" => false, "error" => "Missing code_format bool. Set to true if you want your output wrapped in pre tags.", "errorcode" => "missing_code_format");
      if (!isset($notifyinfo["token"])) return array("success" => false, "error" => "Missing GitHub token", "errorcode" => "missing_github_token");
			return array("success" => true);
		}

		public function Notify($notifykey, &$notifyinfo, $numerrors, &$sinfo, $schedulekey, $name, $userdisp, $data)
		{
			$body = "";
				
			// append the json output if it exists
			if (isset($data["success"])) {
				$jsonData = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
				$body .= str_replace('"', '\"', $jsonData);
			}

			// append a template if there is one
			$template = "/var/scripts/xcron/templates/" . $userdisp . "--" . $name . ".md";
			if (file_exists($template)) {
				$body .= file_get_contents($template);
			}

			// append the log file if it exists
			$log = "/var/log/xcron/" . $userdisp . "--" . $name . ".log";
			if (file_exists($log)) {
				if ($notifyinfo["code_format"]) {
					$body .= "```\n" . file_get_contents($log) . "```\n";	
				} else {
					$body .= file_get_contents($log);	
				}
			}

			// set the title
			$title = (isset($notifyinfo["prefix"]) ? $notifyinfo["prefix"] : "") . $name;

			// build the command to execute
			$cmd = "/usr/bin/gh issue create";
			$cmd .= " -R " . $notifyinfo["repo"];
			if ($notifyinfo["project"] != "") $cmd .= " -p " . "\"" . $notifyinfo["project"] . "\"";
			if ($notifyinfo["assign"] != "") $cmd .= " -a " . $notifyinfo["assign"];
			if ($notifyinfo["label"] != "") $cmd .= " -l " . $notifyinfo["label"];
			$cmd .= " -t " . "\"" . $title . "\"";
			$cmd .= " -b " . "\"" . $body . "\"";

			// execute the command
			putenv($notifyinfo["token"]);
			$output = shell_exec($cmd);

			return array("success" => true);
		}
	}
?>
