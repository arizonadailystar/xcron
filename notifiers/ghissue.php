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
			return array("success" => true);
		}

		public function Notify($notifykey, &$notifyinfo, $numerrors, &$sinfo, $schedulekey, $name, $userdisp, $data)
		{
			$body = "";
				
			// append a template if there is one
			$template = "/var/scripts/xcron/templates/" . $userdisp . "--" . $name . ".md";
			if (file_exists($template)) {
				$body .= file_get_contents($template);
			}

			// append the log file if there is one
			$log = "/var/log/xcron/" . $userdisp . "--" . $name . ".log";
			if (file_exists($log)) {
				if ($notifyinfo["code_format"])
				{
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
			putenv("GH_TOKEN=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");
			$output = shell_exec($cmd);

			return array("success" => true);
		}
	}
?>
